const ensureStylesheet = (id, href) => {
  if (typeof document === "undefined") return;
  if (document.getElementById(id)) return;

  const link = document.createElement("link");
  link.id = id;
  link.rel = "stylesheet";
  link.href = href;
  document.head.appendChild(link);
};

const loadScriptOnce = (id, src) => {
  if (typeof document === "undefined") return Promise.resolve();
  const existing = document.getElementById(id);
  if (existing) {
    return existing.dataset.loaded === "true"
      ? Promise.resolve()
      : new Promise((resolve) => {
          existing.addEventListener("load", () => resolve());
        });
  }

  return new Promise((resolve, reject) => {
    const script = document.createElement("script");
    script.id = id;
    script.src = src;
    script.async = false;
    script.dataset.loaded = "false";
    script.onload = () => {
      script.dataset.loaded = "true";
      resolve();
    };
    script.onerror = (error) => reject(error);
    document.body.appendChild(script);
  });
};

const loadScriptFresh = (id, src) => {
  if (typeof document === "undefined") return Promise.resolve();
  const existing = document.getElementById(id);
  if (existing) existing.remove();

  return new Promise((resolve, reject) => {
    const script = document.createElement("script");
    script.id = id;
    script.src = src;
    script.async = false;
    script.onload = () => resolve();
    script.onerror = (error) => reject(error);
    document.body.appendChild(script);
  });
};

let baseScriptPromise = null;

export const initSecureBookingWidget = async (lang = "en") => {
  ensureStylesheet(
    "hbe-bws-css",
    "https://book.securebookings.net/css/app-v2.css",
  );

  try {
    if (!baseScriptPromise) {
      baseScriptPromise = loadScriptOnce(
        "hbe-bws-all",
        "https://book.securebookings.net/js/v2/widget.all.js",
      );
    }

    await baseScriptPromise;

    // Reload the customize script on every mount to inject widget into fresh DOM
    await loadScriptFresh(
      "hbe-bws-customize",
      `https://book.securebookings.net/widgetCustomize?lang=${lang}&widgetType=Widget&id=d50ad3f2-5720-1764727832-44d9-ade0-c014c61b10e4&ajax=true&cb=${Date.now()}`,
    );
  } catch (error) {
    // eslint-disable-next-line no-console
    console.error("Failed to load secure booking widget", error);
  }
};
