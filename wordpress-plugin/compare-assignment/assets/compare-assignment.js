function openCompareGallery(button) {
  const galleryId = button.getAttribute("aria-controls");
  const galleryRow = document.getElementById(galleryId);

  if (!galleryRow) {
    return;
  }

  galleryRow.hidden = false;
  button.setAttribute("aria-expanded", "true");
  button.textContent = "Hide gallery";
  button.closest(".compare-product-row")?.classList.add("is-open");
}

function closeCompareGallery(button) {
  const galleryId = button.getAttribute("aria-controls");
  const galleryRow = document.getElementById(galleryId);

  if (!galleryRow) {
    return;
  }

  galleryRow.hidden = true;
  button.setAttribute("aria-expanded", "false");
  button.textContent = "Gallery";
  button.closest(".compare-product-row")?.classList.remove("is-open");
}

document.addEventListener("click", (event) => {
  const button = event.target.closest(".compare-gallery-toggle");

  if (!button) {
    return;
  }

  const root = button.closest(".compare-assignment-plugin");
  const isOpen = button.getAttribute("aria-expanded") === "true";

  root?.querySelectorAll(".compare-gallery-toggle[aria-expanded='true']").forEach((openButton) => {
    if (openButton !== button) {
      closeCompareGallery(openButton);
    }
  });

  if (isOpen) {
    closeCompareGallery(button);
  } else {
    openCompareGallery(button);
  }
});
