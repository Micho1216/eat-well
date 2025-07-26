document.addEventListener("DOMContentLoaded", function () {
    const orderNowBtn = document.querySelector(".number-sold-container");

    if (orderNowBtn) {
        orderNowBtn.addEventListener("click", function () {
            const packagesSection = document.getElementById("packages");
            if (packagesSection) {
                packagesSection.scrollIntoView({
                    behavior: "smooth",
                });
            }
        });
    }

    const translationDataElement = document.getElementById("translation-data");
    const breakfastText = translationDataElement.dataset.breakfastText;
    const lunchText = translationDataElement.dataset.lunchText;
    const dinnerText = translationDataElement.dataset.dinnerText;

    const dropdownMenuButton = document.getElementById("dropdownMenuButton");
    const dropdownItems = document.querySelectorAll(".dropdown-item");
    const mealOptionsList = document.getElementById("mealOptions");
    const displayedPrice = document.getElementById("displayedPrice");
    const selectedPackageInput = document.getElementById("selectedPackage");

    let currentPackageData = {};

    // Fungsi untuk memperbarui pilihan makanan dan harga berdasarkan paket yang dipilih
    function updatePackageDetails(packageElement) {
        const packageId = packageElement.dataset.packageId;
        const packageName = packageElement.textContent.trim();
        const breakfastPrice =
            packageElement.dataset.breakfastPrice === "null"
                ? null
                : parseFloat(packageElement.dataset.breakfastPrice);
        const lunchPrice =
            packageElement.dataset.lunchPrice === "null"
                ? null
                : parseFloat(packageElement.dataset.lunchPrice);
        const dinnerPrice =
            packageElement.dataset.dinnerPrice === "null"
                ? null
                : parseFloat(packageElement.dataset.dinnerPrice);

        // Perbarui teks tombol dropdown
        dropdownMenuButton.textContent = packageName;

        // Perbarui input tersembunyi untuk pengiriman formulir
        selectedPackageInput.value = packageName;

        // Simpan data paket saat ini
        currentPackageData = {
            id: packageId,
            name: packageName,
            breakfastPrice: breakfastPrice,
            lunchPrice: lunchPrice,
            dinnerPrice: dinnerPrice,
        };

        // Hapus pilihan makanan yang ada
        mealOptionsList.innerHTML = "";

        // Tambahkan pilihan Sarapan jika tidak null
        if (breakfastPrice !== null) {
            const li = document.createElement("li");
            li.classList.add("list-group-item");
            li.innerHTML = `
              <input class="form-check-input me-1 meal-checkbox" type="checkbox" value="${breakfastPrice}" id="breakfastCheckbox" checked>
              <label class="form-check-label stretched-link" for="breakfastCheckbox">${breakfastText}</label>
          `;
            mealOptionsList.appendChild(li);
        }

        // Tambahkan pilihan Makan Siang jika tidak null
        if (lunchPrice !== null) {
            const li = document.createElement("li");
            li.classList.add("list-group-item");
            li.innerHTML = `
              <input class="form-check-input me-1 meal-checkbox" type="checkbox" value="${lunchPrice}" id="lunchCheckbox" checked>
              <label class="form-check-label stretched-link" for="lunchCheckbox">${lunchText}</label>
          `;
            mealOptionsList.appendChild(li);
        }

        // Tambahkan pilihan Makan Malam jika tidak null
        if (dinnerPrice !== null) {
            const li = document.createElement("li");
            li.classList.add("list-group-item");
            li.innerHTML = `
              <input class="form-check-input me-1 meal-checkbox" type="checkbox" value="${dinnerPrice}" id="dinnerCheckbox" checked>
              <label class="form-check-label stretched-link" for="dinnerCheckbox">${dinnerText}</label>
          `;
            mealOptionsList.appendChild(li);
        }

        // Panggil fungsi untuk memastikan setidaknya satu checkbox aktif
        updateCheckboxStates();
        // Perbarui tampilan harga secara awal
        updateTotalPrice();
    }

    // Fungsi untuk menghitung dan memperbarui total harga
    function updateTotalPrice() {
        let total = 0;
        const checkboxes = mealOptionsList.querySelectorAll(
            ".meal-checkbox:checked"
        );
        checkboxes.forEach((checkbox) => {
            total += parseFloat(checkbox.value);
        });

        let formattedTotal;
        if (total >= 1000) {
            formattedTotal = (total / 1000).toFixed(0) + "k";
        } else {
            formattedTotal = total.toLocaleString("id-ID");
        }
        displayedPrice.textContent = formattedTotal;
    }

    // minimal 1 checkbox yang dicentang, jika hanya satu yang dicentang, nonaktifkan agar tidak bisa di-uncheck
    function updateCheckboxStates() {
        const allCheckboxes =
            mealOptionsList.querySelectorAll(".meal-checkbox");
        const checkedCheckboxes = mealOptionsList.querySelectorAll(
            ".meal-checkbox:checked"
        );

        if (checkedCheckboxes.length === 1) {
            // Jika hanya satu checkbox yang dicentang, nonaktifkan agar tidak bisa di-uncheck
            checkedCheckboxes[0].disabled = true;
        } else {
            // Jika lebih dari satu dicentang, aktifkan semua checkbox
            allCheckboxes.forEach((checkbox) => {
                checkbox.disabled = false;
            });
        }
    }

    // Event listener untuk klik item dropdown
    dropdownItems.forEach((item) => {
        item.addEventListener("click", function () {
            updatePackageDetails(this);
        });
    });

    // Event listener untuk perubahan checkbox (didelegasikan ke mealOptionsList)
    mealOptionsList.addEventListener("change", function (event) {
        if (event.target.classList.contains("meal-checkbox")) {
            updateCheckboxStates(); // Panggil fungsi ini setiap kali checkbox berubah
            updateTotalPrice();
        }
    });

    // Inisialisasi dengan paket pertama saat halaman dimuat
    if (dropdownItems.length > 0) {
        updatePackageDetails(dropdownItems[0]);
    }
});
