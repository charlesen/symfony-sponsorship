import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["feedback", "source", "buttonText"];

  copy(event) {
    let textToCopy = "";

    if (this.hasSourceTarget) {
      textToCopy = this.sourceTarget.value || this.sourceTarget.textContent || "";
    } else if (this.element.dataset.copyContent) {
      textToCopy = this.element.dataset.copyContent;
    }

    if (!textToCopy) return;

    navigator.clipboard.writeText(textToCopy.trim()).then(() => {
      if (this.hasFeedbackTarget) {
        this.feedbackTarget.classList.remove("hidden");
        setTimeout(() => {
          this.feedbackTarget.classList.add("hidden");
        }, 2000);
      }

      if (this.hasButtonTextTarget) {
        const originalText = this.buttonTextTarget.textContent;
        this.buttonTextTarget.textContent = "✓ Copied!";
        setTimeout(() => {
          this.buttonTextTarget.textContent = originalText;
        }, 2000);
      }
    });
  }
}
