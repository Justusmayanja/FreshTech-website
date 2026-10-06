const CART_KEY = "pt-cart";

function readCart() {
  try {
    const data = JSON.parse(localStorage.getItem(CART_KEY) || "[]");
    return Array.isArray(data) ? data : [];
  } catch (err) {
    return [];
  }
}

function writeCart(items) {
  localStorage.setItem(CART_KEY, JSON.stringify(items));
  paintCount();
}

function paintCount() {
  const n = readCart().reduce((sum, item) => sum + item.qty, 0);
  document.querySelectorAll("[data-cart-count]").forEach((el) => {
    el.textContent = String(n);
  });
}

function addToCart(id) {
  const catalog = window.PT_CATALOG || {};
  const product = catalog[id];
  if (!product) return;
  const cart = readCart();
  const found = cart.find((item) => item.id === id);
  if (found) found.qty += 1;
  else cart.push({ id, name: product.name, price: product.price, qty: 1 });
  writeCart(cart);
}

document.addEventListener("click", (event) => {
  const buy = event.target.closest("[data-buy]");
  if (buy) {
    addToCart(buy.getAttribute("data-buy"));
    const dest = document.body.getAttribute("data-cart-page") || "cart.html";
    window.location.href = dest;
    return;
  }

  const toggle = event.target.closest("[data-menu-toggle]");
  if (toggle) {
    document.querySelector("[data-menu]")?.classList.toggle("open");
  }
});

document.querySelectorAll("[data-menu] a").forEach((link) => {
  link.addEventListener("click", () => {
    document.querySelector("[data-menu]")?.classList.remove("open");
  });
});

const OFFICE_MAIL = "pulsetechsolutions.info@gmail.com";

function trapBots(form) {
  if (form.querySelector("[name='_honey']")) return;
  const trap = document.createElement("input");
  trap.type = "text";
  trap.name = "_honey";
  trap.tabIndex = -1;
  trap.autocomplete = "off";
  trap.setAttribute("aria-hidden", "true");
  trap.style.position = "absolute";
  trap.style.left = "-9999px";
  form.appendChild(trap);
}

function showFormNote(form, attr, text) {
  let note = form.querySelector("[" + attr + "]");
  if (!note) {
    note = document.createElement("p");
    note.className = "form-note";
    note.setAttribute(attr, "");
    form.appendChild(note);
  }
  note.hidden = false;
  note.textContent = text;
}

async function sendToOffice(fields) {
  const response = await fetch("https://formsubmit.co/ajax/" + OFFICE_MAIL, {
    method: "POST",
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    body: JSON.stringify(Object.assign({ _captcha: "false", _template: "table" }, fields))
  });
  const data = await response.json().catch(function () { return {}; });
  return response.ok && (data.success === true || data.success === "true");
}

document.querySelectorAll("[data-newsletter], [data-mail-form]").forEach(trapBots);

document.querySelectorAll("[data-newsletter]").forEach((form) => {
  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    const data = new FormData(form);
    if (data.get("_honey")) {
      showFormNote(form, "data-newsletter-note", "Noted. We will be in touch.");
      form.reset();
      return;
    }
    const button = form.querySelector("[type='submit']");
    const label = button ? button.textContent : "";
    if (button) {
      button.disabled = true;
      button.textContent = "Sending";
    }
    let sent = false;
    try {
      sent = await sendToOffice({
        name: data.get("name") || "",
        email: data.get("email") || "",
        _replyto: data.get("email") || "",
        _subject: "Newsletter signup from the website",
        message: "Newsletter signup."
      });
    } catch (err) {
      sent = false;
    }
    if (button) {
      button.disabled = false;
      button.textContent = label;
    }
    if (!sent) {
      showFormNote(form, "data-newsletter-note", "Not sent. Try again.");
      return;
    }
    form.reset();
    showFormNote(form, "data-newsletter-note", "Noted. We will be in touch.");
  });
});

document.querySelectorAll("[data-mail-form]").forEach((form) => {
  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    const data = new FormData(form);
    if (data.get("_honey")) {
      showFormNote(form, "data-mail-note", "Sent. We will reply from this office.");
      form.reset();
      return;
    }
    const button = form.querySelector("[type='submit']");
    const label = button ? button.textContent : "";
    if (button) {
      button.disabled = true;
      button.textContent = "Sending";
    }
    const lines = ["Name: " + (data.get("name") || ""), "Email: " + (data.get("email") || "")];
    if (data.get("phone")) lines.push("Phone: " + data.get("phone"));
    lines.push("", data.get("message") || "");
    let sent = false;
    try {
      sent = await sendToOffice({
        name: data.get("name") || "",
        email: data.get("email") || "",
        phone: data.get("phone") || "",
        message: lines.join("\n"),
        _replyto: data.get("email") || "",
        _subject: data.get("subject") || "Enquiry from the website"
      });
    } catch (err) {
      sent = false;
    }
    if (button) {
      button.disabled = false;
      button.textContent = label;
    }
    if (!sent) {
      showFormNote(form, "data-mail-note", "Not sent. Try again, or WhatsApp +256752895268.");
      return;
    }
    form.reset();
    showFormNote(form, "data-mail-note", "Sent. We will reply from this office.");
  });
});

function renderCart() {
  const mount = document.querySelector("[data-cart-body]");
  if (!mount) return;
  const cart = readCart();
  if (!cart.length) {
    mount.innerHTML = "<p>Your cart is empty. <a href='products.html'>Browse products</a> or pick a package under Website, Graphic, or Marketing.</p>";
    const total = document.querySelector("[data-cart-total]");
    if (total) total.textContent = "$0";
    return;
  }
  const rows = cart.map((item) => {
    const line = item.price * item.qty;
    return `<tr>
      <td>${item.name}</td>
      <td>$${item.price}</td>
      <td><input type="number" min="1" value="${item.qty}" data-qty="${item.id}"></td>
      <td>$${line}</td>
      <td><button type="button" class="remove" data-remove="${item.id}">Remove</button></td>
    </tr>`;
  }).join("");
  mount.innerHTML = `<table class="cart-table">
    <thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Line</th><th></th></tr></thead>
    <tbody>${rows}</tbody>
  </table>`;
  const total = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
  const totalEl = document.querySelector("[data-cart-total]");
  if (totalEl) totalEl.textContent = "$" + total;
}

document.addEventListener("change", (event) => {
  const input = event.target.closest("[data-qty]");
  if (!input) return;
  const id = input.getAttribute("data-qty");
  const qty = Math.max(1, parseInt(input.value, 10) || 1);
  const cart = readCart().map((item) => item.id === id ? { ...item, qty } : item);
  writeCart(cart);
  renderCart();
});

document.addEventListener("click", (event) => {
  const remove = event.target.closest("[data-remove]");
  if (remove) {
    const id = remove.getAttribute("data-remove");
    writeCart(readCart().filter((item) => item.id !== id));
    renderCart();
    return;
  }
  if (event.target.closest("[data-checkout]")) {
    const cart = readCart();
    if (!cart.length) return;
    const lines = cart.map((item) => item.qty + " x " + item.name + " — $" + (item.price * item.qty));
    const total = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
    const text = "Hello PulseTech, I would like to order:\n" + lines.join("\n") + "\nTotal: $" + total + " USD\nI will pay in Uganda shillings at the rate on the invoice.";
    window.open("https://wa.me/256752895268?text=" + encodeURIComponent(text), "_blank", "noopener");
  }
  if (event.target.closest("[data-clear-cart]")) {
    writeCart([]);
    renderCart();
  }
});

const articles = document.getElementById("articles");
function revealArticles() {
  if (articles) articles.hidden = false;
}
if (location.hash.indexOf("article") !== -1) revealArticles();
document.querySelectorAll('a[href^="#article"]').forEach((link) => {
  link.addEventListener("click", revealArticles);
});

document.querySelectorAll(".share-rail a[data-share]").forEach((link) => {
  const page = encodeURIComponent(location.href);
  const network = link.getAttribute("data-share");
  if (network === "facebook") link.href = "https://www.facebook.com/sharer/sharer.php?u=" + page;
  if (network === "twitter") link.href = "https://twitter.com/intent/tweet?url=" + page;
  if (network === "pinterest") link.href = "https://pinterest.com/pin/create/button/?url=" + page;
});

const siteHeader = document.querySelector(".site-header");
if (siteHeader && (document.body.classList.contains("home") || document.querySelector(".page-hero, .site-hero"))) {
  const stickHeader = () => siteHeader.classList.toggle("is-stuck", window.scrollY > 30);
  stickHeader();
  window.addEventListener("scroll", stickHeader, { passive: true });
}

paintCount();
renderCart();

(function () {
  const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  if (reduce || !("IntersectionObserver" in window)) return;
  document.documentElement.classList.add("motion");

  const sections = document.querySelectorAll("main > section, .site-cta");
  const itemSelector = ".pcard, .pkg, .service-card, .quote, .stat, .feature, .blog-showcase-card, .step";
  sections.forEach((section, index) => {
    if (index % 2 === 0) {
      section.classList.add("rise");
      return;
    }
    const heading = section.querySelector("h1, h2");
    if (heading) heading.classList.add("rise-word");
    section.querySelectorAll(itemSelector).forEach((item, itemIndex) => {
      item.classList.add("rise-item");
      item.style.transitionDelay = (itemIndex % 4) * 0.22 + "s";
    });
  });

  const watcher = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      entry.target.classList.toggle("in", entry.isIntersecting);
    });
  }, { threshold: 0, rootMargin: "0px 0px -12% 0px" });

  document.querySelectorAll(".rise, .rise-word, .rise-item").forEach((el) => watcher.observe(el));
})();
