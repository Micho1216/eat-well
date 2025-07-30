function openModal() {
    document.getElementById("addCategoryModal").classList.remove("hidden");
    document.getElementById("modalBackdrop").classList.remove("hidden");
}

function closeModal() {
    document.getElementById("addCategoryModal").classList.add("hidden");
    document.getElementById("modalBackdrop").classList.add("hidden");

    // Clear the input value and remove validation state
    const input = document.getElementById("categoryName");
    input.value = "";
    input.classList.remove("is-invalid");

    // Also remove any error message if present
    const errorFeedback = input.nextElementSibling;
    if (errorFeedback && errorFeedback.classList.contains("invalid-feedback")) {
        errorFeedback.innerText = "";
    }
}

function openDeleteModal(categoryId) {
    const form = document.getElementById("deleteCategoryForm");
    form.action = `/categories/${categoryId}`; // adjust route prefix if needed
    document.getElementById("deleteCategoryModal").classList.remove("hidden");
    document.getElementById("deleteModalBackdrop").classList.remove("hidden");
}

function closeDeleteModal() {
    document.getElementById("deleteCategoryModal").classList.add("hidden");
    document.getElementById("deleteModalBackdrop").classList.add("hidden");
}

function handleDeleteClick(categoryId, packageCount) {
    if (packageCount > 0) {
        openCannotDeleteModal();
    } else {
        openDeleteModal(categoryId);
    }
}

function openCannotDeleteModal() {
    document.getElementById("cannotDeleteModal").classList.remove("hidden");
    document.getElementById("cannotDeleteBackdrop").classList.remove("hidden");
}

function closeCannotDeleteModal() {
    document.getElementById("cannotDeleteModal").classList.add("hidden");
    document.getElementById("cannotDeleteBackdrop").classList.add("hidden");
}
