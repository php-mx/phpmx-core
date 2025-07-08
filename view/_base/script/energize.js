document.addEventListener("DOMContentLoaded", () => {
  window.onpopstate = () => location.reload();
  document.body.querySelectorAll("script:not([static])").forEach((tag) => tag.setAttribute("static", ""));
  energize.core.run();
  energize.alert(currentAlert);
  console.log("⚡");
});

const app = {};

const energize = {};

energize.working = false;
energize.registred = false;

energize.core = {
  registred: {},
  instanceVue: {},
  run() {
    Object.keys(energize.core.registred).forEach((querySelector) =>
      document.body.querySelectorAll(querySelector).forEach((element) => {
        energize.core.registred[querySelector](element);
        element.setAttribute("static", "");
      })
    );
    document.body.querySelectorAll("script:not([static])").forEach((tag) => {
      eval(tag.innerHTML);
      tag.setAttribute("static", "");
    });
  },
  register(querySelector, action) {
    energize.core.registred[querySelector] = action;
  },
  request(url = null, method = "get", data = {}, header = {}, useWorking = true) {
    return new Promise(function (resolve, reject) {
      if (useWorking && energize.working) return reject("working");

      if (useWorking) energize.working = true;
      document.body.classList.add("__working__");

      var xhr = new XMLHttpRequest();

      url = url ?? window.location.href;

      xhr.open(method, url, true);
      xhr.setRequestHeader("Request-Partial", true);

      for (let key in header) xhr.setRequestHeader(key, header[key]);

      xhr.responseType = "json";

      xhr.onload = () => {
        if (useWorking) energize.working = false;
        document.body.classList.remove("__working__");

        let resp = xhr.response;

        if (!resp.info || !resp.info.mx)
          resp = {
            info: {
              mx: false,
              error: xhr.status > 399,
              staus: xhr.status,
            },
            data: resp,
          };

        if (resp.info.alert ?? false) energize.alert(resp.info.alert);

        if (resp.info.location ?? false) {
          energize.go(resp.info.location, true);
          return reject("redirect");
        }

        return resolve(resp);
      };

      xhr.send(data);
    });
  },
  loadScript(src) {
    return new Promise(function (resolve, reject) {
      if (document.head.querySelectorAll(`script[src="${src}"]`).length) return resolve();
      let script = document.createElement("script");
      script.async = "true";
      script.src = src;
      script.onload = () => resolve();
      document.head.appendChild(script);
    });
  },
};

energize.update = {
  content(content) {
    let element = document.getElementById("CONTENT");

    Object.entries(energize.core.instanceVue).forEach(([id, app]) => {
      const el = document.getElementById(id);
      if (el && element.contains(el)) {
        app.unmount();
        delete energize.core.instanceVue[id];
      }
    });

    element.innerHTML = content;
    energize.core.run();
  },
  layout(content, state) {
    let element = document.getElementById("LAYOUT");

    Object.entries(energize.core.instanceVue).forEach(([id, app]) => {
      const el = document.getElementById(id);
      if (el && element.contains(el)) {
        app.unmount();
        delete energize.core.instanceVue[id];
      }
    });

    element.innerHTML = content;
    element.dataset.state = state;
    energize.core.run();
  },
  location(url) {
    if (url != window.location) history.pushState({ urlPath: url }, null, url);
  },
  head(head) {
    document.title = head.title;
    document.head.querySelector('meta[name="description"]').setAttribute("content", head.description);
    document.head.querySelector('link[rel="icon"]').setAttribute("href", head.favicon);
  },
};

energize.go = (url, force = false) => {
  if (!force && url == window.location) return;
  if (new URL(url).hostname != new URL(window.location).hostname) return energize.redirect(url);
  let state = document.getElementById("LAYOUT").dataset.state;
  energize.core
    .request(url, "get", {}, { "Layout-State": state })
    .then((resp) => {
      if (!resp.info.mx) return energize.redirect(url);

      if (resp.info.error) return;

      energize.update.head(resp.data.head);

      energize.update.location(url);

      if (resp.data.state == state) {
        energize.update.content(resp.data.content);
      } else {
        energize.update.layout(resp.data.content, resp.data.state);
      }

      window.scrollTo(0, 0);
      return;
    })
    .catch(() => null);
};

energize.redirect = (url) => {
  window.location.href = url;
  return false;
};

energize.alert = (listAlert) => {
  let div = document.getElementById("ALERT");
  listAlert.forEach((item) => {
    let title = item[0] ?? "";
    let content = item[1] ?? "";
    let type = item[2] ?? "";
    let svg = {
      neutral: `[#ICON:alert-neutral]`,
      success: `[#ICON:alert-success]`,
      error: `[#ICON:alert-error]`,
    }[type];
    let alert = `<div class="${type}">${svg}<span>${title}</span><span>${content}</span></div>`;
    div.insertAdjacentHTML("beforeend", alert);
  });
  div.querySelectorAll("div:not([static])").forEach((e) => {
    e.setAttribute("static", "");
    setTimeout(function () {
      e.remove();
    }, 5000);
  });
};

energize.copy = (copyText, alertText = null) => {
  document.body.classList.add("__copying__");
  let copy = document.getElementById("COPY");
  copy.value = copyText;
  copy.select();
  copy.setSelectionRange(0, 99999);
  document.execCommand("copy");
  document.body.classList.remove("__copying__");
  if (alertText) energize.alert([[alertText, "", "success"]]);
};

energize.debounce = (func, wait) => {
  let timer = null;
  return () => {
    clearTimeout(timer);
    timer = setTimeout(func, wait);
  };
};

energize.submit = (form, appentData = {}) => {
  const notify = form.dataset.notify ? document.getElementById(form.dataset.notify) : form.querySelector("[data-notify]");

  if (notify) notify.innerHTML = "";

  form.querySelectorAll("[data-error]").forEach((el) => {
    el.removeAttribute("data-error");
  });

  let url = form.action;
  let state = document.getElementById("LAYOUT").dataset.state;
  let header = { "Layout-State": state };
  let data = new FormData(form);

  appentData.formKey = form.getAttribute("data-form-key");
  for (const [key, value] of Object.entries(appentData)) data.append(key, value);

  // ⚠️ Suporte a arquivos ainda não implementado no backend
  //   form.querySelectorAll("input[type=file]").forEach((input) => {
  //     for (var i = 0; i < input.files.length; i++) {
  //       if (input.getAttribute("name")) {
  //         data.append(input.getAttribute("name") + "[]", input.files[i]);
  //       }
  //     }
  //   });

  energize.core
    .request(url, form.getAttribute("method") ?? "post", data, header)
    .then((resp) => {
      if (resp.info.error && form.dataset.error) return eval(form.dataset.error)(resp);

      if (!resp.info.error && form.dataset.success) return eval(form.dataset.success)(resp);

      if (resp.data) {
        energize.update.head(resp.data.head);
        energize.update.location(url);
        if (resp.data.state == state) energize.update.content(resp.data.content);
        else energize.update.layout(resp.data.content, resp.data.state);
        window.scrollTo(0, 0);
        return;
      }

      if (resp.info.error && resp.info.field) {
        const label = form.querySelector(`[data-input="${resp.info.field}"]`);
        if (label) label.setAttribute("data-error", resp.info.message ?? "true");
      }

      if (notify) {
        let spanClass = `sts_` + (resp.info.error ? "erro" : "success");
        let message = resp.info.message ?? (resp.info.error ? "erro" : "ok");
        notify.innerHTML = `<span class='${spanClass}'>${message}</span>`;
      }
    })
    .catch(() => null);
};

energize.vue = (component, elementId) => {
  energize.core
    .loadScript("/assets/third/vue.js")
    .then(() => {
      if (energize.core.instanceVue[elementId]) {
        energize.core.instanceVue[elementId].unmount();
        delete energize.core.instanceVue[elementId];
      }
      energize.core.instanceVue[elementId] = Vue.createApp(component());
      energize.core.instanceVue[elementId].mount(`#${elementId}`);
    })
    .catch((e) => console.error("impossible to load [vue.js]", e));
};

energize.encapsulate = (value) => {
  return JSON.stringify(value);
};

energize.decapsulate = (value) => {
  return JSON.parse(value);
};

energize.api = (url, method, data) => {
  return energize.core.request(url, method, data, { "Request-Api": true }, false);
};

energize.uid = () => {
  return "_" + Date.now().toString(36) + Math.random().toString(36).substr(2);
};

energize.importFieldValue = (fieldId) => {
  return energize.decapsulate(document.getElementById(fieldId).value);
};

energize.exportFieldValue = (fieldId, value) => {
  document.getElementById(fieldId).value = energize.encapsulate(value);
};

energize.submitForm = (formId) => {
  document.getElementById(formId).requestSubmit();
};

energize.core.register("[href]:not([static]):not([href=''])", (element) => {
  element.addEventListener("click", (event) => {
    event.preventDefault();
    let url = new URL(element.href ?? element.getAttribute("href"), document.baseURI).href;
    energize.go(url, document.baseURI);
  });
});

energize.core.register("[href]:not([href=''])", (element) => {
  let url = new URL(element.href ?? element.getAttribute("href"), document.baseURI).href + "/";
  let href = window.location.href + "/";

  element.classList.remove("active-link");
  element.classList.remove("current-link");

  if (href.startsWith(url)) element.classList.add("active-link");
  if (url == href) element.classList.add("current-link");
});

energize.core.register("form[data-form-key]:not([static])", (element) => {
  element.addEventListener("submit", async (ev) => {
    ev.preventDefault();
    energize.submit(element);
  });
});
