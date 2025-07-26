document.addEventListener("DOMContentLoaded", function () {
    let lastCardStars = null; // Track last clicked card stars
    let lastCardStarsIdx = -1;
    let lastOrderId = null;

    // Card stars
    document.querySelectorAll(".card-order").forEach(function (card) {
        const stars = card.querySelectorAll(".star-icon-btn");
        let selected = -1;

        stars.forEach((star, idx) => {
            star.addEventListener("click", function () {
                selected = idx;
                updateStars(idx);
                lastCardStars = stars;
                lastCardStarsIdx = idx;
                lastOrderId = card.dataset.orderId; // Make sure you set data-order-id on .card-order
                openRateReviewModal(idx + 1);
            });
            star.addEventListener("mouseenter", function () {
                updateStars(idx);
            });
        });

        let rating_list = card.querySelector(".rating-icon-list");
        if(rating_list){
            card.querySelector(".rating-icon-list").addEventListener(
                "mouseleave",
                function () {
                    updateStars(selected);
                }
            );
        }
        function updateStars(activeIdx) {
            stars.forEach((s, i) => {
                if (i <= activeIdx) {
                    s.classList.add("choosen");
                } else {
                    s.classList.remove("choosen");
                }
            });
        }
    });

    // Manual Modal logic
    const modal = document.getElementById("rateReviewModal");
    const modalStars = modal.querySelectorAll(".star-icon-modal");
    let modalSelected = -1;
    
    modalStars.forEach((star, idx) => {
        star.addEventListener("click", function () {
            modalSelected = idx;
            updateModalStars(idx);
        });
        star.addEventListener("mouseenter", function () {
            updateModalStars(idx);
        });
    });
    modal
        .querySelector(".rating-icon-list-modal")
        .addEventListener("mouseleave", function () {
            updateModalStars(modalSelected);
        });

    function updateModalStars(activeIdx) {
        modalStars.forEach((s, i) => {
            if (i <= activeIdx) {
                s.classList.add("choosen");
            } else {
                s.classList.remove("choosen");
            }
        });
    }

    // Open modal and set stars
    window.openRateReviewModal = function (starValue) {
        modalSelected = starValue - 1;
        updateModalStars(modalSelected);
        modal.querySelector("#reviewText").value = "";
        modal.style.display = "flex";
        document.body.style.overflow = "hidden";
    };

    // Reset card stars
    function resetCardStars() {
        if (lastCardStars) {
            lastCardStars.forEach((s) => s.classList.remove("choosen"));
        }
        lastCardStars = null;
        lastCardStarsIdx = -1;
        lastOrderId = null;
    }

    // Close modal (close/cancel/outside)
    function closeModalAndReset() {
        modal.style.display = "none";
        document.body.style.overflow = "";
        resetCardStars();
    }

    document.getElementById("closeRateReviewModal").onclick =
        document.getElementById("cancelRateReviewModal").onclick =
            closeModalAndReset;

    modal.addEventListener("click", function (e) {
        if (e.target === modal) {
            closeModalAndReset();
        }
    });

    // Success modal logic
    const successModal = document.getElementById("successModal");
    function showSuccessModal() {
        successModal.style.display = "flex";
        document.body.style.overflow = "hidden";
    }
    function closeSuccessModal() {
        successModal.style.display = "none";
        document.body.style.overflow = "";
        // Optionally reload page or update UI
        window.location.reload();
    }
    document.getElementById("closeSuccessModal").onclick =
        document.getElementById("okSuccessModal").onclick = closeSuccessModal;

    // Submit review
    modal.querySelector(".btn-primary").onclick = function () {
        const rating = modalSelected + 1;
        const review = modal.querySelector("#reviewText").value;
        const orderId = lastOrderId;
        if (!orderId) return;

        fetch(`/orders/${orderId}/review`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            body: JSON.stringify({ rating, review }),
        })
            .then((res) => res.json())
            .then((data) => {
                modal.style.display = "none";
                document.body.style.overflow = "";
                showSuccessModal();
            });
    };

    // Cancel Modal logic
    const cancelModal = document.getElementById("cancelModal");
    const cancelForm = document.getElementById("cancelForm");
    const closeModalBtn = document.getElementById("closeModalBtn");

    document.querySelectorAll(".open-cancel-modal").forEach(function(btn) {
        btn.addEventListener("click", function(e) {
            console.log("masuk");
            e.preventDefault();
            const orderId = btn.getAttribute("data-order-id");
            // Set the form action dynamically (adjust route as needed)
            cancelForm.action = `/orders/${orderId}/cancel`;
            cancelModal.classList.remove("hidden");
            cancelModal.style.display = "flex";
            document.body.style.overflow = "hidden";
        });
    });

    closeModalBtn.addEventListener("click", function() {
        cancelModal.classList.add("hidden");
        cancelModal.style.display = "none";
        document.body.style.overflow = "";
    });

    // Optional: Close modal when clicking outside content
    cancelModal.addEventListener("click", function(e) {
        if (e.target === cancelModal) {
            cancelModal.classList.add("hidden");
            cancelModal.style.display = "none";
            document.body.style.overflow = "";
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const flash = document.getElementById('flash-message');
    if (flash) {
        setTimeout(() => {
            flash.classList.add('fade-out');
            setTimeout(() => flash.remove(), 500);
        }, 3000);
    }
});