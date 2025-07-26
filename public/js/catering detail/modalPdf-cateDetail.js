document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("pdfModal");
    const iframe = document.getElementById("pdfFrame");
    const closeModal = document.querySelector(".close");

    // Handle semua tombol View
    document.querySelectorAll(".view-menu-text").forEach((viewBtn) => {
        viewBtn.addEventListener("click", function () {
            const pdfUrl = this.dataset.pdf;  
            iframe.src = pdfUrl;
            modal.style.display = "flex";
        });
    });

    // Handle semua tombol Download
    document.querySelectorAll(".download-wrapper").forEach((downloadBtn) => {
        downloadBtn.addEventListener("click", function () {
            const pdfUrl = this.dataset.pdf;
            const link = document.createElement("a");
            link.href = pdfUrl;
            link.download = pdfUrl.split("/").pop();
            link.click();
        });
    });

    // Close modal
    closeModal.addEventListener("click", () => {
        modal.style.display = "none";
        iframe.src = "";
    });

    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
            iframe.src = "";
        }
    });
});