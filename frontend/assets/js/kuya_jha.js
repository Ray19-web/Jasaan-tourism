(() => {
    const widget = document.querySelector(".kuya-jha");

    if (!widget) {
        return;
    }

    const toggle = widget.querySelector(".kuya-jha__toggle");
    const panel = widget.querySelector(".kuya-jha__panel");
    const closeButton = widget.querySelector(".kuya-jha__close");
    const form = widget.querySelector(".kuya-jha__form");
    const input = widget.querySelector("textarea");
    const messages = widget.querySelector(".kuya-jha__messages");
    const submitButton = form.querySelector("button");
    const BASE_URL = window.BASE_URL || "/jasaan-tourism";

    function setOpen(isOpen) {
        panel.hidden = !isOpen;
        toggle.setAttribute("aria-expanded", String(isOpen));
        if (isOpen) {
            input.focus();
        }
    }

    function addMessage(text, role) {
        const message = document.createElement("div");
        message.className = `kuya-jha__message kuya-jha__message--${role}`;
        message.textContent = text;
        messages.appendChild(message);
        messages.scrollTop = messages.scrollHeight;
        return message;
    }

    function formatAssistantMessage(text) {
        const escaped = String(text || "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");

        return escaped
            .replace(/\*\*(.+?)\*\*/g, '<strong class="kuya-jha__highlight">$1</strong>')
            .replace(/(^|\n)\s*[*-]\s+/g, '$1<span class="kuya-jha__bullet">&#8226;</span> ')
            .replace(/\n/g, "<br>");
    }

    toggle.addEventListener("click", () => setOpen(panel.hidden));
    closeButton.addEventListener("click", () => setOpen(false));

    input.addEventListener("keydown", (event) => {
        if (event.key === "Enter" && !event.shiftKey && !event.isComposing) {
            event.preventDefault();

            if (!submitButton.disabled) {
                form.requestSubmit();
            }
        }
    });

    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        const question = input.value.trim();

        if (!question) {
            input.focus();
            return;
        }

        addMessage(question, "visitor");
        input.value = "";
        submitButton.disabled = true;
        const pending = addMessage("Thinking...", "assistant");

        try {
            const response = await fetch(`${BASE_URL}/backend/kuya_jha.php`, {
                method: "POST",
                headers: { "Content-Type": "application/json", "Accept": "application/json" },
                body: JSON.stringify({ message: question })
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || "OW-J is unavailable right now.");
            }

            pending.innerHTML = formatAssistantMessage(data.answer);
        } catch (error) {
            pending.textContent = error.message || "OW-J is unavailable right now.";
        } finally {
            submitButton.disabled = false;
            input.focus();
            messages.scrollTop = messages.scrollHeight;
        }
    });
})();
