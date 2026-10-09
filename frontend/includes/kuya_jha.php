<aside class="kuya-jha" aria-label="OW-J tourism assistant">
    <button class="kuya-jha__toggle" type="button" aria-expanded="false" aria-controls="kuyaJhaPanel">
        <i class="fa-solid fa-comments" aria-hidden="true"></i>
        <span>Ask Kuya OW-J</span>
    </button>

    <section class="kuya-jha__panel" id="kuyaJhaPanel" hidden>
        <header class="kuya-jha__header">
            <div>
                <span class="kuya-jha__eyebrow">JASAAN TOURISM GUIDE</span>
                <h2>Kuya OW-J</h2>
                <p class="kuya-jha__tagline">One Jasaan Wow Jasaan</p>
            </div>
            <button class="kuya-jha__close" type="button" aria-label="Close OW-J">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </header>
        <div class="kuya-jha__messages" aria-live="polite">
            <div class="kuya-jha__message kuya-jha__message--assistant">Hi! I am Kuya OW-J. Ask me about Jasaan places, travel tips, estimated costs, or the USD equivalent.</div>
        </div>
        <form class="kuya-jha__form">
            <label class="sr-only" for="kuyaJhaInput">Ask Kuya OW-J</label>
            <textarea id="kuyaJhaInput" rows="2" maxlength="800" placeholder="Ask about a place in Jasaan..."></textarea>
            <button type="submit" aria-label="Send question">
                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
            </button>
        </form>
    </section>
</aside>
