document.addEventListener("DOMContentLoaded", function () {
    const items = document.querySelectorAll(".dropdown-item");
    const button = document.getElementById("dropdownMenuButton");
    const hiddenInput = document.getElementById("selectedPackage");

    items.forEach((item) => {
        item.addEventListener("click", function () {
            const selectedText = this.textContent;
            button.textContent = selectedText;
            hiddenInput.value = selectedText;
        });
    });
});

$(document).ready(function () {
    const translationDataElement = document.getElementById("translation-data");
    const packageText = translationDataElement.dataset.packageText;
    const packagesText = translationDataElement.dataset.packagesText;
    const itemsText = translationDataElement.dataset.itemsText;
    const noPackageSelectedYetText = translationDataElement.dataset.noPackageSelectedYet;

    $(".add-button").click(function (e) {
        var accordionItem = $(this).attr("data-tab");
        $("#" + accordionItem)
            .slideToggle()
            .parent()
            .siblings()
            .find(".accordion-content")
            .slideUp();

        $(this).toggleClass("active-title");
        $("#" + accordionItem)
            .parent()
            .siblings()
            .find(".accordion-title")
            .removeClass("active-title");
    });

    // Add CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    const summary = {
        totalItems: 0,
        totalPrice: 0,
        packages: {},
    };

    // Function to initialize cart from backend
    function initializeCartFromBackend() {
        // You need to get the vendorId. This could be from a meta tag, a hidden input, or a JS variable defined by Blade.
        // For example, if you add <meta name="vendor-id" content="{{ $vendor->vendorId }}"> in your Blade view.
        const vendorId = $('meta[name="vendor-id"]').attr("content");

        if (!vendorId) {
            console.error("Vendor ID is missing. Cannot load cart.");
            return;
        }

        $.ajax({
            url: "/load-cart",
            method: "GET",
            data: {
                vendor_id: vendorId, // Pass vendor_id to identify the cart
            },
            success: function (response) {
                summary.totalItems = response.totalItems;
                summary.totalPrice = response.totalPrice;
                // summary.packages = response.packages;
                summary.packages = {};

                for (const pkgId in response.packages) {
                    if (response.packages.hasOwnProperty(pkgId)) {
                        const pkgData = response.packages[pkgId];
                        summary.packages[pkgId] = {
                            id: pkgData.id,
                            items: pkgData.items || {}, // Pastikan items selalu objek, bahkan jika kosong dari backend
                        };
                    }
                }

                // Update UI based on loaded data
                for (const pkgId in summary.packages) {
                    const pkgData = summary.packages[pkgId];
                    const accordionContentId = `item${pkgId}`; // Assuming this is how your HTML IDs are structured
                    const addButton = $(
                        `.add-button[data-tab="${accordionContentId}"]`
                    );
                    const addText = addButton.find(".add-text");

                    // let currentPackageItemCount = 0;
                    // for (const itemName in pkgData.items) {
                    //     const qty = pkgData.items[itemName];
                    //     currentPackageItemCount += qty;

                    //     // Update the quantity display in the accordion content
                    //     const itemRow = $(
                    //         `#${accordionContentId} .item-row:has(span:contains('${itemName}'))`
                    //     );
                    //     itemRow.find(".qty").text(qty);
                    // }

                    let currentPackageItemCount = 0;
                    for (const mealTypeKey in pkgData.items) {
                        const qty = pkgData.items[mealTypeKey];
                        currentPackageItemCount += qty;

                        const itemRow = $(
                            `#${accordionContentId} .item-row span[data-meal-type="${mealTypeKey}"]`
                        ).closest('.item-row');

                        if (itemRow.length) {
                            itemRow.find(".qty").text(qty);
                        } else {
                            console.warn(`Element with data-meal-type="${mealTypeKey}" not found for package ${pkgId}.`);
                        }
                    }

                    // Update the "Add" button text
                    if (currentPackageItemCount === 0) {
                        addText.text("Add");
                        addButton.removeClass("active");
                    } else if (currentPackageItemCount === 1) {
                        addText.text(`${currentPackageItemCount} Item`);
                        addButton.addClass("active");
                    } else {
                        addText.text(`${currentPackageItemCount} ${itemsText}`);
                        addButton.addClass("active");
                    }
                }
                updateSummaryDisplay();
            },
            error: function (xhr, status, error) {
                console.error("Error loading cart:", error);
            },
        });
    }

    function updateSummaryDisplay() {
        const pkgCount = Object.keys(summary.packages).length;
        const proceedToPaymentLink = $('#proceedToPaymentLink'); 

        if (pkgCount === 0) {
            $(".order-message").show().text(`${noPackageSelectedYetText}`);
            $(".package-count, .item-count, .price-total").hide();
            proceedToPaymentLink.css({
                'cursor': 'default',
                'pointer-events': 'none',
            });
        } else if (pkgCount === 1) {
            $(".order-message").hide();
            $(".package-count").show().text(`${pkgCount} ${packageText}`);
            if (summary.totalItems === 1) {
                $(".item-count").show().text(`${summary.totalItems} Item`);
            } else {
                $(".item-count").show().text(`${summary.totalItems} ${itemsText}`);
            }
            $(".price-total")
                .show()
                .text(`Rp. ${summary.totalPrice.toLocaleString("id-ID")}`);
            
            proceedToPaymentLink.css({
                'cursor': 'pointer',
                'pointer-events': 'auto',
            });
        } else {
            $(".order-message").hide();
            $(".package-count").show().text(`${pkgCount} ${packagesText}`);
            $(".item-count").show().text(`${summary.totalItems} Items`);
            $(".price-total")
                .show()
                .text(`Rp. ${summary.totalPrice.toLocaleString("id-ID")}`);

            proceedToPaymentLink.css({
                'cursor': 'pointer',
                'pointer-events': 'auto',
            });
        }
    }

    // Fungsi untuk mengirim data ke server melalui AJAX
    function sendOrderUpdateToServer() {
        const vendorId = $('meta[name="vendor-id"]').attr("content"); // <--- PENTING: Apakah meta tag ini ada dan benar?
        if (!vendorId) {
            console.error("Vendor ID is missing. Cannot send order update.");
            return; // Jika vendorId kosong, AJAX request tidak akan terkirim
        }

        console.log("Mengirim request AJAX ke backend...");
        console.log("Current summary.packages to be sent:", JSON.stringify(summary.packages, null, 2));

        $.ajax({
            url: "/update-order-summary", // Ini akan menjadi rute Laravel baru Anda
            method: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"), // Token CSRF Laravel
                packages: summary.packages,
                vendor_id: vendorId, // pass vendor_id to backend
            },
            success: function (response) {
                // Perbarui summary sisi klien dengan data dari server
                summary.totalItems = response.totalItems;
                summary.totalPrice = response.totalPrice;
                // Anda juga mungkin ingin memperbarui total per paket individual jika Anda memilikinya
                // di objek summary sisi klien, tetapi untuk saat ini, kita hanya peduli dengan total keseluruhan.

                updateSummaryDisplay(); // Perbarui tampilan dengan total yang dihitung server
            },
            error: function (xhr, status, error) {
                console.error(
                    "Kesalahan saat memperbarui ringkasan pesanan:",
                    error
                );
                // Opsional, tampilkan pesan kesalahan kepada pengguna
            },
        });
    }

    $(".add-button").click(function () {
        const tabId = $(this).attr("data-tab");
        const content = $("#" + tabId);

        $(".add-button").not(this).removeClass("active");
    });

    $(".accordion-content").on("click", ".increment, .decrement", function () {
        const isInc = $(this).hasClass("increment");
        const qtySpan = $(this).siblings(".qty");
        let qty = parseInt(qtySpan.text());
        // const price = parseFloat(
        //     $(this).closest(".item-row").find(".price").data("price")
        // );
        const pkgId = $(this)
            .closest(".accordion-item")
            .find(".accordion-title")
            .data("package-id");
        const pkgAccordionContentId = $(this)
            .closest(".accordion-content")
            .attr("id"); // Ambil ID dari accordion-content

        if (isInc) qty++;
        else if (qty > 0) qty--;

        qtySpan.text(qty);

        if (!summary.packages[pkgId]) {
            summary.packages[pkgId] = {
                id: pkgId,
                items: {},
                // total: 0, // Total ini akan dihitung di server
            };
            console.log(`Initialized new package entry for pkgId: ${pkgId}`);
        }

        const itemName = $(this).closest(".item-row").find("span:first").data("meal-type");
        summary.packages[pkgId].items[itemName] = qty;

        // console.log("DEBUG: pkgId saat ini:", pkgId);
        // console.log("DEBUG: itemName saat ini:", itemName);
        // console.log("DEBUG: qty saat ini:", qty);
        // console.log("DEBUG: price dari data-price:", price);
        // console.log("DEBUG: Struktur summary.packages saat ini:", JSON.stringify(summary.packages, null, 2));

        // Hapus item jika qty adalah 0
        if (qty === 0){
            delete summary.packages[pkgId].items[itemName];
            console.log(`Deleted item ${itemName} from pkgId ${pkgId} as qty is 0.`);
        }

        // Jika sebuah paket tidak memiliki item yang dipilih, hapus dari summary
        // if (Object.keys(summary.packages[pkgId]?.items || {}).length === 0) {
        //     // Gunakan optional chaining
        //     delete summary.packages[pkgId];
        // }

        let currentPackageItemCount = 0;
        if (summary.packages[pkgId]) {
            for (let item in summary.packages[pkgId].items) {
                currentPackageItemCount += summary.packages[pkgId].items[item];
            }
        }
        console.log(`Current total active items in pkgId ${pkgId}: ${currentPackageItemCount}`);

        // Ini adalah logika untuk menghapus seluruh paket dari summary
        // Hanya hapus pkgId dari summary.packages jika tidak ada item aktif yang tersisa di dalamnya.
        if (currentPackageItemCount === 0) {
            delete summary.packages[pkgId];
            console.log(`Deleted pkgId ${pkgId} from summary.packages as it has no active items.`);
        }

        // Perbarui label tombol "Add" (bagian ini masih bisa di sisi klien)
        // Temukan 'add-button' yang benar yang terkait dengan paket yang sedang dimodifikasi.
        const addButton = $(`.add-button[data-tab="${pkgAccordionContentId}"]`);
        const addText = addButton.find(".add-text");

        if (currentPackageItemCount === 0) {
            addText.text("Add");
            addButton.removeClass("active");
            // delete summary.packages[pkgId];
        } else if (currentPackageItemCount === 1) {
            addText.text(`${currentPackageItemCount} Item`);
            addButton.addClass("active");
        } else {
            addText.text(`${currentPackageItemCount} Items`); // Sesuaikan teks
            addButton.addClass("active");
        }

        console.log("Summary.packages after local update:", JSON.stringify(summary.packages, null, 2));
        // Kirim summary yang diperbarui ke server
        sendOrderUpdateToServer();
    });

    // Keadaan awal
    // updateSummaryDisplay();
    initializeCartFromBackend();
});