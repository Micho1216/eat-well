document.addEventListener("DOMContentLoaded", function () {
    //   const items = document.querySelectorAll(".dropdown-item");
    const addresText = document.getElementById("location-txt");
    const hiddenInput = document.getElementById("selected-address-for-vendor");

    document
        .querySelectorAll(".location-dropdown .dropdown-item")
        .forEach((item) => {
            item.addEventListener("click", function (e) {
                e.preventDefault();

                const addressId = this.getAttribute("data-address-id");
                document.getElementById("address-id-input").value = addressId;
                document.getElementById("address-form").submit();
            });
        });
});

document.querySelectorAll('input[name="rating"]').forEach(function (radio) {
    radio.addEventListener("mousedown", function (e) {
        if (this.checked) {
            this.wasChecked = true;
        } else {
            this.wasChecked = false;
        }
    });
    radio.addEventListener("click", function (e) {
        if (this.wasChecked) {
            this.checked = false;
            // Optional: trigger change event if you rely on it
            this.dispatchEvent(new Event("change"));
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    var openBtn = document.getElementById("openFilterBtn");
    var closeBtn = document.getElementById("closeFilterBtn");
    var popup = document.getElementById("filterPopup");
    if (openBtn && closeBtn && popup) {
        openBtn.addEventListener("click", function () {
            popup.classList.add("active");
            document.body.style.overflow = "hidden";
        });
        closeBtn.addEventListener("click", function () {
            popup.classList.remove("active");
            document.body.style.overflow = "";
        });
    }
});

// Allow unchecking sort radio button when clicked again
document.querySelectorAll('input[name="sort"]').forEach(function (radio) {
    radio.addEventListener("mousedown", function (e) {
        if (this.checked) {
            this.wasChecked = true;
        } else {
            this.wasChecked = false;
        }
    });
    radio.addEventListener("click", function (e) {
        if (this.wasChecked) {
            this.checked = false;
            this.dispatchEvent(new Event("change"));
        }
    });
});
