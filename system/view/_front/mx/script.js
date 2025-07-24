document.addEventListener("DOMContentLoaded", () => {
    window.onpopstate = () => location.reload();
    document.body.querySelectorAll("script:not([static])").forEach((tag) => tag.setAttribute("static", ""));
    mx.core.run();
    mx.alert(currentAlert);
});

const app = {};

const mx = {};

mx.working = false;
mx.registred = false;

mx.core = {
    registred: {},
    instanceVue: {},
    run() {
        Object.keys(mx.core.registred).forEach((querySelector) =>
            document.body.querySelectorAll(querySelector).forEach((element) => {
                mx.core.registred[querySelector](element);
                element.setAttribute("static", "");
            })
        );
        document.body.querySelectorAll("script:not([static])").forEach((tag) => {
            eval(tag.innerHTML);
            tag.setAttribute("static", "");
        });
    },
    register(querySelector, action) {
        mx.core.registred[querySelector] = action;
    },
    request(url = null, method = "get", data = {}, header = {}, useWorking = true) {
        return new Promise(function (resolve, reject) {
            if (useWorking && mx.working) return reject("working");

            if (useWorking) mx.working = true;
            document.body.classList.add("__working__");

            var xhr = new XMLHttpRequest();

            url = url ?? window.location.href;

            xhr.open(method, url, true);
            xhr.setRequestHeader("Request-Partial", true);

            for (let key in header) xhr.setRequestHeader(key, header[key]);

            xhr.responseType = "json";

            xhr.onload = () => {
                if (useWorking) mx.working = false;
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

                if (resp.info.alert ?? false) mx.alert(resp.info.alert);

                if (resp.info.location ?? false) {
                    mx.go(resp.info.location, true);
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

mx.update = {
    content(content) {
        let element = document.getElementById("CONTENT");

        Object.entries(mx.core.instanceVue).forEach(([id, app]) => {
            const el = document.getElementById(id);
            if (el && element.contains(el)) {
                app.unmount();
                delete mx.core.instanceVue[id];
            }
        });

        element.innerHTML = content;
        mx.core.run();
    },
    layout(content, state) {
        let element = document.getElementById("LAYOUT");

        Object.entries(mx.core.instanceVue).forEach(([id, app]) => {
            const el = document.getElementById(id);
            if (el && element.contains(el)) {
                app.unmount();
                delete mx.core.instanceVue[id];
            }
        });

        element.innerHTML = content;
        element.dataset.state = state;
        mx.core.run();
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

mx.go = (url, force = false) => {
    if (!force && url == window.location) return;
    if (new URL(url).hostname != new URL(window.location).hostname) return mx.redirect(url);
    let state = document.getElementById("LAYOUT").dataset.state;
    mx.core
        .request(url, "get", {}, { "Layout-State": state })
        .then((resp) => {
            if (!resp.info.mx) return mx.redirect(url);

            if (resp.info.error) return;

            mx.update.head(resp.data.head);

            mx.update.location(url);

            if (resp.data.state == state) {
                mx.update.content(resp.data.content);
            } else {
                mx.update.layout(resp.data.content, resp.data.state);
            }

            window.scrollTo(0, 0);
            return;
        })
        .catch(() => null);
};

mx.redirect = (url) => {
    window.location.href = url;
    return false;
};

mx.alert = (listAlert) => {
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

mx.copy = (copyText, alertText = null) => {
    const textarea = document.createElement("textarea");
    textarea.value = copyText;
    textarea.setAttribute("readonly", "");
    textarea.style.position = "absolute";
    textarea.style.left = "-9999px";

    document.body.appendChild(textarea);

    textarea.select();
    textarea.setSelectionRange(0, 99999);
    document.execCommand("copy");

    document.body.removeChild(textarea);

    if (alertText) mx.alert([[alertText, "", "success"]]);
};

mx.debounce = (func, wait) => {
    let timer = null;
    return () => {
        clearTimeout(timer);
        timer = setTimeout(func, wait);
    };
};

mx.submit = (form, appentData = {}) => {
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

    mx.core
        .request(url, form.getAttribute("method") ?? "post", data, header)
        .then((resp) => {
            if (resp.info.error && form.dataset.error) return eval(form.dataset.error)(resp);

            if (!resp.info.error && form.dataset.success) return eval(form.dataset.success)(resp);

            if (resp.data) {
                mx.update.head(resp.data.head);
                mx.update.location(url);
                if (resp.data.state == state) mx.update.content(resp.data.content);
                else mx.update.layout(resp.data.content, resp.data.state);
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

mx.vue = (component, elementId) => {
    mx.core
        .loadScript("/assets/third/vue.js")
        .then(() => {
            if (mx.core.instanceVue[elementId]) {
                mx.core.instanceVue[elementId].unmount();
                delete mx.core.instanceVue[elementId];
            }
            mx.core.instanceVue[elementId] = Vue.createApp(component());
            mx.core.instanceVue[elementId].mount(`#${elementId}`);
        })
        .catch((e) => console.error("impossible to load [vue.js]", e));
};

mx.encapsulate = (value) => {
    return JSON.stringify(value);
};

mx.decapsulate = (value) => {
    return JSON.parse(value);
};

mx.api = (url, method, data) => {
    return mx.core.request(url, method, data, { "Request-Api": true }, false);
};

mx.uid = () => {
    return "_" + Date.now().toString(36) + Math.random().toString(36).substr(2);
};

mx.importFieldValue = (fieldId) => {
    return mx.decapsulate(document.getElementById(fieldId).value);
};

mx.exportFieldValue = (fieldId, value) => {
    document.getElementById(fieldId).value = mx.encapsulate(value);
};

mx.submitForm = (formId) => {
    document.getElementById(formId).requestSubmit();
};

mx.core.register("[href]:not([static]):not([href=''])", (element) => {
    element.addEventListener("click", (event) => {
        event.preventDefault();
        let url = new URL(element.href ?? element.getAttribute("href"), document.baseURI).href;
        mx.go(url, document.baseURI);
    });
});

mx.core.register("[href]:not([href=''])", (element) => {
    let url = new URL(element.href ?? element.getAttribute("href"), document.baseURI).href + "/";
    let href = window.location.href + "/";

    element.classList.remove("active-link");
    element.classList.remove("current-link");

    if (href.startsWith(url)) element.classList.add("active-link");
    if (url == href) element.classList.add("current-link");
});

mx.core.register("form[data-form-key]:not([static])", (element) => {
    element.addEventListener("submit", async (ev) => {
        ev.preventDefault();
        mx.submit(element);
    });
});
