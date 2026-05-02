function openGallery(button) {
  const galleryId = button.getAttribute("aria-controls");
  const galleryRow = document.getElementById(galleryId);

  if (!galleryRow) {
    return;
  }

  galleryRow.hidden = false;
  button.setAttribute("aria-expanded", "true");
  button.textContent = "Hide gallery";
  button.closest(".product-row")?.classList.add("is-open");
}

function closeGallery(button) {
  const galleryId = button.getAttribute("aria-controls");
  const galleryRow = document.getElementById(galleryId);

  if (!galleryRow) {
    return;
  }

  galleryRow.hidden = true;
  button.setAttribute("aria-expanded", "false");
  button.textContent = "Gallery";
  button.closest(".product-row")?.classList.remove("is-open");
}

document.addEventListener("click", (event) => {
  const button = event.target.closest(".gallery-toggle");

  if (!button) {
    return;
  }

  const isOpen = button.getAttribute("aria-expanded") === "true";

  document.querySelectorAll(".gallery-toggle[aria-expanded='true']").forEach((openButton) => {
    if (openButton !== button) {
      closeGallery(openButton);
    }
  });

  if (isOpen) {
    closeGallery(button);
  } else {
    openGallery(button);
  }
});
