document.addEventListener("DOMContentLoaded", () => {
  const flash = document.querySelector("[data-autohide-flash]");
  if (flash) {
    window.setTimeout(() => {
      flash.style.opacity = "0";
      flash.style.transform = "translateY(-6px)";
    }, 4500);
  }

  const titleField = document.querySelector("[data-slug-source]");
  const slugField = document.querySelector("[data-slug-target]");
  if (titleField && slugField) {
    let slugTouched = slugField.value.trim() !== "";
    slugField.addEventListener("input", () => {
      slugTouched = slugField.value.trim() !== "";
    });
    titleField.addEventListener("input", () => {
      if (slugTouched) {
        return;
      }

      slugField.value = titleField.value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "");
    });
  }

  document.querySelectorAll("[data-copy-text]").forEach((button) => {
    button.addEventListener("click", async () => {
      const text = button.getAttribute("data-copy-text") || "";
      if (!text) {
        return;
      }

      try {
        await navigator.clipboard.writeText(text);
        const original = button.textContent;
        button.textContent = "Copied";
        window.setTimeout(() => {
          button.textContent = original;
        }, 1800);
      } catch (error) {
        window.prompt("Copy this URL", text);
      }
    });
  });

  document.querySelectorAll("[data-fill-target]").forEach((button) => {
    button.addEventListener("click", () => {
      const selector = button.getAttribute("data-fill-target");
      const value = button.getAttribute("data-fill-value") || "";
      if (!selector) {
        return;
      }

      const target = document.querySelector(selector);
      if (!target) {
        return;
      }

      target.value = value;
      target.dispatchEvent(new Event("input", { bubbles: true }));
      target.scrollIntoView({ behavior: "smooth", block: "center" });
      target.focus();
    });
  });

  if (window.CodeMirror) {
    const cmOptions = (mode) => ({
      mode,
      theme: "dracula",
      lineNumbers: true,
      lineWrapping: true,
      tabSize: 2,
      indentWithTabs: false,
      autofocus: false,
      extraKeys: { Tab: (cm) => cm.replaceSelection("  ") },
    });

    document.querySelectorAll("[data-codemirror='html']").forEach((el) => {
      CodeMirror.fromTextArea(el, cmOptions("htmlmixed"));
    });
    document.querySelectorAll("[data-codemirror='css']").forEach((el) => {
      CodeMirror.fromTextArea(el, cmOptions("css"));
    });
    document.querySelectorAll("[data-codemirror='js']").forEach((el) => {
      CodeMirror.fromTextArea(el, cmOptions("javascript"));
    });
    document.querySelectorAll("[data-codemirror='json']").forEach((el) => {
      CodeMirror.fromTextArea(el, { ...cmOptions("application/json"), mode: "application/json" });
    });
  }

  if (window.ClassicEditor) {
    document.querySelectorAll("[data-ckeditor]").forEach((element) => {
      window.ClassicEditor.create(element, {
        toolbar: [
          "heading",
          "|",
          "bold",
          "italic",
          "link",
          "bulletedList",
          "numberedList",
          "blockQuote",
          "|",
          "insertTable",
          "undo",
          "redo"
        ]
      }).catch((error) => {
        console.error("CKEditor failed to load", error);
      });
    });
  }

  const builderShell = document.querySelector("[data-builder-shell]");
  if (builderShell) {
    const list = builderShell.querySelector("[data-builder-list]");
    const picker = document.querySelector("[data-widget-picker]");
    const addButton = document.querySelector("[data-add-widget]");
    const hiddenField = document.querySelector("#builder_json");
    const seedNode = document.querySelector("#cms-builder-seed");
    const catalogNode = document.querySelector("#cms-builder-catalog");

    const parseJson = (node, fallback) => {
      if (!node) return fallback;
      try {
        return JSON.parse(node.textContent || node.value || "");
      } catch (error) {
        return fallback;
      }
    };

    const state = Array.isArray(parseJson(seedNode, [])) ? parseJson(seedNode, []) : [];
    const defaultsRaw = parseJson(catalogNode, {});
    const widgetLabels = {
      hero: "Hero",
      text: "Text",
      image: "Image",
      video: "Video",
      cta: "CTA",
      faq: "FAQ",
      pricing: "Pricing",
      testimonials: "Testimonials",
      stats: "Stats",
      service_cards: "Service Cards",
      buttons: "Buttons",
      custom_html: "Custom HTML"
    };

    const field = (label, input) => `
      <label class="builder-field">
        <span>${label}</span>
        ${input}
      </label>
    `;

    const textArea = (value, rows = 4) =>
      `<textarea rows="${rows}" class="builder-input">${String(value ?? "")}</textarea>`;

    const textInput = (value) =>
      `<input type="text" class="builder-input" value="${String(value ?? "").replace(/"/g, "&quot;")}" />`;

    const renderItems = (items, columns, mapFn) => {
      const rows = Array.isArray(items) && items.length > 0 ? items : [mapFn(null, 0, true)];
      return `
        <div class="builder-sublist" data-array-items>
          ${rows.map((item, index) => `
            <div class="builder-subitem" data-subitem>
              <div class="builder-subitem-grid cols-${columns}">
                ${mapFn(item, index, false)}
              </div>
              <button type="button" class="btn btn-danger btn-sm" data-remove-subitem>Remove</button>
            </div>
          `).join("")}
        </div>
      `;
    };

    const itemTemplate = {
      faq: () => ({ question: "Question", answer: "Answer" }),
      pricing: () => ({ name: "Package", price: "$499", details: "Short summary", features: ["Feature one", "Feature two"] }),
      testimonials: () => ({ quote: "Client quote", name: "Client Name", role: "Owner" }),
      stats: () => ({ number: "250%", label: "Growth" }),
      service_cards: () => ({ title: "Service", text: "Description" }),
      buttons: () => ({ label: "Button", url: "/pages/contact" })
    };

    const defaultPayload = (type) => JSON.parse(JSON.stringify(defaultsRaw[type] || { heading: "New section" }));

    const renderPayload = (type, payload) => {
      switch (type) {
        case "hero":
          return `
            <div class="builder-grid cols-2">
              ${field("Eyebrow", textInput(payload.eyebrow))}
              ${field("Heading", textInput(payload.heading))}
              ${field("Highlight", textInput(payload.highlight))}
              ${field("Background image URL", textInput(payload.background_image))}
              ${field("Primary button label", textInput(payload.primary_label))}
              ${field("Primary button URL", textInput(payload.primary_url))}
              ${field("Secondary button label", textInput(payload.secondary_label))}
              ${field("Secondary button URL", textInput(payload.secondary_url))}
            </div>
            ${field("Hero text", textArea(payload.text, 5))}
          `;
        case "text":
          return `${field("Heading", textInput(payload.heading))}${field("Body HTML", textArea(payload.body, 10))}`;
        case "image":
          return `<div class="builder-grid cols-2">${field("Heading", textInput(payload.heading))}${field("Image URL", textInput(payload.image_url))}${field("Alt text", textInput(payload.alt))}${field("Caption", textInput(payload.caption))}</div>`;
        case "video":
          return `<div class="builder-grid cols-2">${field("Heading", textInput(payload.heading))}${field("Video URL", textInput(payload.video_url))}${field("Poster URL", textInput(payload.poster_url))}${field("Caption", textInput(payload.caption))}</div>`;
        case "cta":
          return `${field("Heading", textInput(payload.heading))}${field("Text", textArea(payload.text, 5))}<div class="builder-grid cols-2">${field("Button label", textInput(payload.button_label))}${field("Button URL", textInput(payload.button_url))}</div>`;
        case "faq":
          return `${field("Heading", textInput(payload.heading))}${renderItems(payload.items, 2, (item, index, bare) => {
            const seed = bare ? itemTemplate.faq() : item;
            return `${field("Question", textInput(seed.question))}${field("Answer", textArea(seed.answer, 4))}`;
          })}<button type="button" class="btn btn-secondary btn-sm" data-add-subitem data-subtype="faq">Add FAQ</button>`;
        case "pricing":
          return `${field("Heading", textInput(payload.heading))}${renderItems(payload.items, 4, (item, index, bare) => {
            const seed = bare ? itemTemplate.pricing() : item;
            return `${field("Name", textInput(seed.name))}${field("Price", textInput(seed.price))}${field("Details", textArea(seed.details, 3))}${field("Features (one per line)", textArea(Array.isArray(seed.features) ? seed.features.join("\n") : "", 4))}`;
          })}<button type="button" class="btn btn-secondary btn-sm" data-add-subitem data-subtype="pricing">Add Package</button>`;
        case "testimonials":
          return `${field("Heading", textInput(payload.heading))}${renderItems(payload.items, 3, (item, index, bare) => {
            const seed = bare ? itemTemplate.testimonials() : item;
            return `${field("Quote", textArea(seed.quote, 4))}${field("Name", textInput(seed.name))}${field("Role", textInput(seed.role))}`;
          })}<button type="button" class="btn btn-secondary btn-sm" data-add-subitem data-subtype="testimonials">Add Testimonial</button>`;
        case "stats":
          return `${field("Heading", textInput(payload.heading))}${renderItems(payload.items, 2, (item, index, bare) => {
            const seed = bare ? itemTemplate.stats() : item;
            return `${field("Number", textInput(seed.number))}${field("Label", textInput(seed.label))}`;
          })}<button type="button" class="btn btn-secondary btn-sm" data-add-subitem data-subtype="stats">Add Stat</button>`;
        case "service_cards":
          return `${field("Heading", textInput(payload.heading))}${renderItems(payload.items, 2, (item, index, bare) => {
            const seed = bare ? itemTemplate.service_cards() : item;
            return `${field("Title", textInput(seed.title))}${field("Text", textArea(seed.text, 4))}`;
          })}<button type="button" class="btn btn-secondary btn-sm" data-add-subitem data-subtype="service_cards">Add Card</button>`;
        case "buttons":
          return `${field("Heading", textInput(payload.heading))}${renderItems(payload.items, 2, (item, index, bare) => {
            const seed = bare ? itemTemplate.buttons() : item;
            return `${field("Label", textInput(seed.label))}${field("URL", textInput(seed.url))}`;
          })}<button type="button" class="btn btn-secondary btn-sm" data-add-subitem data-subtype="buttons">Add Button</button>`;
        case "custom_html":
          return `${field("Heading", textInput(payload.heading))}${field("Trusted HTML", textArea(payload.html, 10))}`;
        default:
          return `${field("Heading", textInput(payload.heading))}`;
      }
    };

    const extractPayload = (card, type) => {
      const inputs = card.querySelectorAll(".builder-input");
      const get = (index) => inputs[index] ? inputs[index].value : "";

      switch (type) {
        case "hero":
          return { eyebrow: get(0), heading: get(1), highlight: get(2), background_image: get(3), primary_label: get(4), primary_url: get(5), secondary_label: get(6), secondary_url: get(7), text: get(8) };
        case "text":
          return { heading: get(0), body: get(1) };
        case "image":
          return { heading: get(0), image_url: get(1), alt: get(2), caption: get(3) };
        case "video":
          return { heading: get(0), video_url: get(1), poster_url: get(2), caption: get(3) };
        case "cta":
          return { heading: get(0), text: get(1), button_label: get(2), button_url: get(3) };
        case "faq":
        case "pricing":
        case "testimonials":
        case "stats":
        case "service_cards":
        case "buttons": {
          const heading = get(0);
          const items = [];
          card.querySelectorAll("[data-subitem]").forEach((subitem) => {
            const vals = Array.from(subitem.querySelectorAll(".builder-input")).map((node) => node.value);
            if (type === "faq") items.push({ question: vals[0] || "", answer: vals[1] || "" });
            if (type === "pricing") items.push({ name: vals[0] || "", price: vals[1] || "", details: vals[2] || "", features: (vals[3] || "").split("\n").map((v) => v.trim()).filter(Boolean) });
            if (type === "testimonials") items.push({ quote: vals[0] || "", name: vals[1] || "", role: vals[2] || "" });
            if (type === "stats") items.push({ number: vals[0] || "", label: vals[1] || "" });
            if (type === "service_cards") items.push({ title: vals[0] || "", text: vals[1] || "" });
            if (type === "buttons") items.push({ label: vals[0] || "", url: vals[1] || "" });
          });
          return { heading, items };
        }
        case "custom_html":
          return { heading: get(0), html: get(1) };
        default:
          return { heading: get(0) };
      }
    };

    const sync = () => {
      const rows = [];
      list.querySelectorAll("[data-widget-card]").forEach((card) => {
        const type = card.getAttribute("data-widget-type") || "text";
        rows.push({ widget_type: type, payload: extractPayload(card, type) });
      });
      hiddenField.value = JSON.stringify(rows);
    };

    const cardTemplate = (type, payload) => {
      const label = widgetLabels[type] || type;
      return `
        <article class="builder-card" data-widget-card data-widget-type="${type}">
          <div class="builder-card-head">
            <strong>${label}</strong>
            <div class="inline-actions">
              <button type="button" class="btn btn-ghost btn-sm" data-move-up><i class="fas fa-arrow-up"></i></button>
              <button type="button" class="btn btn-ghost btn-sm" data-move-down><i class="fas fa-arrow-down"></i></button>
              <button type="button" class="btn btn-danger btn-sm" data-remove-widget><i class="fas fa-trash"></i></button>
            </div>
          </div>
          <div class="builder-card-body">${renderPayload(type, payload)}</div>
        </article>
      `;
    };

    const mount = () => {
      list.innerHTML = state.map((row) => cardTemplate(row.widget_type, row.payload || defaultPayload(row.widget_type))).join("");
      sync();
    };

    const addWidget = (type, payload = null) => {
      state.push({ widget_type: type, payload: payload || defaultPayload(type) });
      mount();
    };

    addButton?.addEventListener("click", () => {
      addWidget(picker?.value || "text");
    });

    document.querySelectorAll("[data-add-template-widget]").forEach((button) => {
      button.addEventListener("click", () => {
        const type = button.getAttribute("data-template-type") || "text";
        let payload = defaultPayload(type);
        try {
          payload = JSON.parse(button.getAttribute("data-template-payload") || "{}");
        } catch (error) {}
        addWidget(type, payload);
      });
    });

    list.addEventListener("click", (event) => {
      const target = event.target.closest("button");
      if (!target) return;
      const card = target.closest("[data-widget-card]");
      if (!card) return;
      const cards = Array.from(list.querySelectorAll("[data-widget-card]"));
      const index = cards.indexOf(card);
      if (index < 0) return;

      if (target.hasAttribute("data-remove-widget")) {
        state.splice(index, 1);
        mount();
      }
      if (target.hasAttribute("data-move-up") && index > 0) {
        [state[index - 1], state[index]] = [state[index], state[index - 1]];
        mount();
      }
      if (target.hasAttribute("data-move-down") && index < state.length - 1) {
        [state[index + 1], state[index]] = [state[index], state[index + 1]];
        mount();
      }
      if (target.hasAttribute("data-add-subitem")) {
        const subtype = target.getAttribute("data-subtype");
        const row = state[index];
        row.payload.items = Array.isArray(row.payload.items) ? row.payload.items : [];
        row.payload.items.push(itemTemplate[subtype] ? itemTemplate[subtype]() : {});
        mount();
      }
      if (target.hasAttribute("data-remove-subitem")) {
        const subitem = target.closest("[data-subitem]");
        const row = state[index];
        const subitems = Array.from(card.querySelectorAll("[data-subitem]"));
        const subIndex = subitems.indexOf(subitem);
        if (subIndex >= 0 && Array.isArray(row.payload.items)) {
          row.payload.items.splice(subIndex, 1);
          mount();
        }
      }
    });

    list.addEventListener("input", () => {
      sync();
    });

    mount();
  }
});
