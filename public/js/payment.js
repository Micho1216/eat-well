document.addEventListener("DOMContentLoaded", function () {
    const translationDataElement = document.getElementById("translation-data");
    const wellpayBalanceTextTranslate = translationDataElement.dataset.wellpayBalanceText;
    const wellpayConfirmBtnTranslate = translationDataElement.dataset.wellpayConfirmBtn;
    const selectPaymethTextTranslate = translationDataElement.dataset.selectPaymethText;
    const noPaymethTextTranslate = translationDataElement.dataset.noPaymethText;
    const missingOrderTextTranslate = translationDataElement.dataset.missingOrderText;
    const unknownErrorTextTranslate = translationDataElement.dataset.unknownErrorText;
    const checkoutFailedTextTranslate = translationDataElement.dataset.checkoutFailedText;
    const wellpayInsufficientTextTranslate = translationDataElement.dataset.wellpayInsufficientText;
    const wellpayFormatErrorTextTranslate = translationDataElement.dataset.wellpayFormatErrorText;
    const insufficientWellpayBalanceTextTranslate = translationDataElement.dataset.insufficientWellpayBalanceText;
    const yourBalanceTextTranslate = translationDataElement.dataset.yourBalanceText;
    const amountPayTextTranslate = translationDataElement.dataset.amountPayText;
    const retrieveWellpayBalanceTextTranslate = translationDataElement.dataset.retrieveWellpayBalanceText;
    const retrieveCheckConnectionTextTranslate = translationDataElement.dataset.retrieveCheckConnectionText;
    const enterPassTextTranslate = translationDataElement.dataset.enterPassText;
    const wellpayCancelTextTranslate = translationDataElement.dataset.wellpayCancelText;
    const continueTextTranslate = translationDataElement.dataset.continueText;


    // --- Get DOM Elements ---
    const qrisRadio = document.getElementById("qris");
    const wellpayRadio = document.getElementById("wellpay");
    const mainPayButton = document.getElementById("mainPayButton");

    // Popups elements
    const qrisPopup = document.getElementById("qrisPopup");
    const qrisPopUpCancelled = document.getElementById("qrisPopupCancelled");
    const wellpayConfirmPopup = document.getElementById("wellpayConfirmPopup");
    const confirmationPopup = document.getElementById("confirmationPopup"); // General confirmation popup
    const successPopup = document.getElementById("successPopup");
    const customMessageBox = document.getElementById("customMessageBox");

    // Elements inside popups
    const doneBtn = document.getElementById("doneBtn");
    const downloadQrisBtn = document.getElementById("downloadQrisBtn");
    const qrCodeImage = document.getElementById("qrCodeImage");
    const expiresInMess = document.getElementById('expiresInMess');
    const countdownTimerElement = document.getElementById("countdownTimer");
    const messageBoxText = document.getElementById("messageBoxText");
    const messageBoxOkBtn = document.getElementById("messageBoxOkBtn");
    const wellpayBalanceText = document.getElementById("wellpayBalanceText");
    const closeBtn = document.getElementById("closeBtn");

    // Elements for Wellpay popup stages
    const wellpayStage1 = document.getElementById("wellpayStage1");
    const wellpayStage2 = document.getElementById("wellpayStage2");
    const wellpayAmountToPay = document.getElementById("wellpayAmountToPay"); // Untuk menampilkan total bayar di stage 2

    // Elements for wellpayConfirmPopup (password input & error)
    const wellpayPasswordInput = document.getElementById(
        "wellpayPasswordInput"
    );
    const wellpayPasswordError = document.getElementById(
        "wellpayPasswordError"
    );
    const wellpayPopupMessage = document.getElementById("wellpayPopupMessage"); // Untuk pesan umum di dalam modal Wellpay

    const wellpayConfirmBtn = document.getElementById("wellpayConfirmBtn");
    const wellpayCancelBtn = document.getElementById("wellpayCancelBtn");
    const confirmBtn = document.getElementById("confirmBtn"); // Button for general confirmation popup
    const backHomeBtn = document.getElementById("backHomeBtn");

    const hiddenVendorId = document.getElementById("hiddenVendorId");
    const hiddenStartDate = document.getElementById("hiddenStartDate");
    const hiddenEndDate = document.getElementById("hiddenEndDate");
    const hiddenCartTotalPrice = document.getElementById(
        "hiddenCartTotalPrice"
    );

    let timerInterval;
    let timeLeft = 59; // Initial time for QRIS countdown

    // Store total price to use across functions
    let totalOrderPrice = parseFloat(hiddenCartTotalPrice?.value || 0);
    // Tambahkan variabel status untuk Wellpay popup
    let currentWellpayPopupStage = "initial_confirm"; // States: 'initial_confirm', 'password_input'

    // --- Helper Functions for Popups and Messages ---
    function formatRupiah(number) {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(number);
    }

    /** Displays a custom message box. */
    function showMessage(message) {
        if (messageBoxText && customMessageBox) {
            messageBoxText.innerHTML = message;
            customMessageBox.classList.add("active");
        } else {
            console.error(
                "Message box elements not found. Defaulting to alert."
            );
            alert(message); // Fallback if elements are missing
        }
    }

    /** Hides the custom message box. */
    function hideMessageBox() {
        if (customMessageBox) {
            customMessageBox.classList.remove("active");
        }
    }

    /** Starts the QRIS countdown timer. */
    function startTimer() {
        clearInterval(timerInterval); // Clear any existing timer
        timeLeft = 59; // Reset time
        if (countdownTimerElement) {
            let minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;
            countdownTimerElement.textContent = `${
                minutes < 10 ? "0" : ""
            }${minutes}:${seconds < 10 ? "0" : ""}${seconds}`;
        }
        timerInterval = setInterval(() => {
            timeLeft--;
            if (countdownTimerElement) {
                if (timeLeft < 0) {
                    clearInterval(timerInterval);
                    hideQrisPopup();
                    qrisPopUpCancelled.classList.add("active");
                    if(closeBtn){
                        closeBtn.addEventListener("click", function(){
                            qrisPopUpCancelled.classList.remove("active");
                        });
                        qrisRadio.checked = false;
                    }
                } else {
                    let minutes = Math.floor(timeLeft / 60);
                    let seconds = timeLeft % 60;
                    countdownTimerElement.textContent = `${
                        minutes < 10 ? "0" : ""
                    }${minutes}:${seconds < 10 ? "0" : ""}${seconds}`;
                }
            }
        }, 1000);
    }

    /** Shows the QRIS payment popup. */
    function showQrisPopup() {
        if (qrisPopup) {
            if (qrCodeImage) {
                qrCodeImage.src = "/asset/payment/qris.jpg";
            }
            qrisPopup.classList.add("active");
            startTimer();
        }
    }

    /** Hides the QRIS payment popup. */
    function hideQrisPopup() {
        if (qrisPopup) {
            qrisPopup.classList.remove("active");
            clearInterval(timerInterval); // Stop timer when popup is hidden
        }
    }

    /** Shows the Wellpay confirmation popup and sets to initial stage. */
    function showWellpayConfirmPopup(balance) {
        if (
            wellpayConfirmPopup &&
            wellpayBalanceText &&
            wellpayStage1 &&
            wellpayStage2 &&
            wellpayPasswordInput
        ) {
            wellpayBalanceText.textContent = `${wellpayBalanceTextTranslate}: ${formatRupiah(
                balance
            )}`;

            // Reset to initial state (Stage 1)
            currentWellpayPopupStage = "initial_confirm";
            wellpayStage1.style.display = "block"; // Show initial message
            wellpayStage2.style.display = "none"; // Hide password input
            wellpayConfirmBtn.textContent = `${wellpayConfirmBtnTranslate}`; // Button text for Stage 1

            // Clear password input and all messages within this popup
            wellpayPasswordInput.value = "";
            wellpayPasswordError.style.display = "none";
            wellpayPopupMessage.style.display = "none"; // Clear previous general message

            wellpayConfirmPopup.classList.add("active"); // Show the popup
        }
    }

    /** Hides the Wellpay confirmation popup and resets stage. */
    function hideWellpayConfirmPopup() {
        if (wellpayConfirmPopup) {
            wellpayConfirmPopup.classList.remove("active");
            // Reset stage on hide
            currentWellpayPopupStage = "initial_confirm";
            if (wellpayStage1) wellpayStage1.style.display = "block";
            if (wellpayStage2) wellpayStage2.style.display = "none";
            if (wellpayConfirmBtn) wellpayConfirmBtn.textContent = `${wellpayConfirmBtnTranslate}`;
            // Also clear password and messages when closing
            if (wellpayPasswordInput) wellpayPasswordInput.value = "";
            if (wellpayPasswordError)
                wellpayPasswordError.style.display = "none";
            if (wellpayPopupMessage) wellpayPopupMessage.style.display = "none";
        }
    }

    /** Shows the general confirmation popup. */
    function showGeneralConfirmPopup() {
        if (confirmationPopup) {
            confirmationPopup.classList.add("active");
        }
    }

    /** Hides the general confirmation popup. */
    function hideGeneralConfirmPopup() {
        if (confirmationPopup) {
            confirmationPopup.classList.remove("active");
        }
    }

    /** Shows the success popup. */
    function showSuccessPopup() {
        if (successPopup) {
            successPopup.classList.add("active");
        }
    }

    // --- Centralized AJAX Function for Payment Processing ---
    /**
     * Handles the AJAX call to the backend for payment processing.
     * This function is called after relevant popups are confirmed.
     */
    async function processPaymentAjax(password = null) {
        const selectedMethod = document.querySelector(
            'input[name="payment-button"]:checked'
        );
        if (!selectedMethod) {
            showMessage(`${noPaymethTextTranslate}`);
            return;
        }

        

        const methodId = selectedMethod.value;
        const vendorId = document.getElementById("hiddenVendorId")?.value;
        const startDate = document.getElementById("hiddenStartDate")?.value;
        const endDate = document.getElementById("hiddenEndDate")?.value;
        const provinsi = document.getElementById("hiddenSelectedAddressProvinsi")?.value;
        const kota = document.getElementById("hiddenSelectedAddressKota")?.value;
        const kabupaten = document.getElementById("hiddenSelectedAddressKabupaten")?.value;
        const kecamatan = document.getElementById("hiddenSelectedAddressKecamatan")?.value;
        const kelurahan = document.getElementById("hiddenSelectedAddressKelurahan")?.value;
        const kode_pos = document.getElementById("hiddenSelectedAddressKodePos")?.value;
        const jalan = document.getElementById("hiddenSelectedAddressJalan")?.value;
        const recipient_name = document.getElementById("hiddenSelectedAddressRecipientName")?.value;
        const recipient_phone = document.getElementById("hiddenSelectedAddressRecipientPhone")?.value;
        const notes = document.getElementById("hiddenSelectedAddressNotes")?.value;

        if (!vendorId || !startDate || !endDate) {
            showMessage(`${missingOrderTextTranslate}`);
            console.error("Missing essential order details for AJAX:", {
                vendorId,
                startDate,
                endDate,
            });
            return;
        }

        const postData = {
            _token: $('meta[name="csrf-token"]').attr("content"),
            payment_method_id: methodId,
            vendor_id: vendorId,
            start_date: startDate,
            end_date: endDate,
            provinsi: provinsi,
            kota: kota,
            kabupaten: kabupaten,
            kecamatan: kecamatan,
            kelurahan: kelurahan,
            kode_pos: kode_pos,
            jalan: jalan,
            recipient_name: recipient_name,
            recipient_phone: recipient_phone,
            notes: notes,
        };

        // Add password if payment method is Wellpay
        const WELLPAY_METHOD_ID = "1";
        if (methodId === WELLPAY_METHOD_ID) {
            postData.password = password;
        }

        try {
            const response = await $.ajax({
                url: window.App.routes.checkoutProcess,
                method: "POST",
                data: postData,
                dataType: "json",
            });

            console.log("Checkout Response:", response);
            if (response.message === "Checkout successful!") {
                hideWellpayConfirmPopup();
                showSuccessPopup();
            } else {
                wellpayPopupMessage.textContent =
                    response.message ||
                    `${unknownErrorTextTranslate}`;
                wellpayPopupMessage.classList.remove("text-success");
                wellpayPopupMessage.classList.add("text-danger");
                wellpayPopupMessage.style.display = "block";
                // If it's a success but with a strange message, close the popup anyway
                hideWellpayConfirmPopup();
            }
        } catch (xhr) {
            // This block is for HTTP status code errors (4xx, 5xx)
            console.error("Checkout failed:", xhr);
            // Clear previous error/status messages within the Wellpay popup
            wellpayPasswordError.style.display = "none";
            wellpayPasswordError.textContent = "";
            wellpayPopupMessage.style.display = "none";
            wellpayPopupMessage.textContent = "";

            const responseJson = xhr.responseJSON;
            const errorMessage =
                responseJson?.message ||
                xhr.responseText ||
                `${checkoutFailedTextTranslate}`;

            if (xhr.status === 422 && responseJson?.errors?.password) {
                // CASE: INCORRECT PASSWORD - STATUS 422 AND HAS 'password' ERROR
                wellpayPasswordInput.value = ""; // Clear password input
                wellpayPasswordError.textContent =
                    responseJson.errors.password[0]; // Set error message from backend
                wellpayPasswordError.style.display = "block"; // Display the error message
            } else if (
                xhr.status === 402 &&
                responseJson?.message ===
                    `${wellpayInsufficientTextTranslate}`
            ) {
                // CASE: INSUFFICIENT BALANCE
                wellpayPopupMessage.textContent = errorMessage;
                wellpayPopupMessage.classList.remove("text-success");
                wellpayPopupMessage.classList.add("text-danger");
                wellpayPopupMessage.style.display = "block";
                hideWellpayConfirmPopup(); // Close Wellpay popup
                showMessage(errorMessage); // Also show in general message box
            } else {
                // CASE: OTHER ERRORS (Network, Server 500, Other Validation)
                wellpayPopupMessage.textContent = errorMessage;
                wellpayPopupMessage.classList.remove("text-success");
                wellpayPopupMessage.classList.add("text-danger");
                wellpayPopupMessage.style.display = "block";
                hideWellpayConfirmPopup(); // Close Wellpay popup
                showMessage(errorMessage); // Also show in general message box
            }
        }
    }

    // --- Event Listeners ---

    // Message Box OK button
    if (messageBoxOkBtn) {
        messageBoxOkBtn.addEventListener("click", hideMessageBox);
    }

    // Main Pay Button (Memanggil Wellpay Confirmation Popup)
    if (mainPayButton) {
        mainPayButton.addEventListener("click", async () => {
            const selectedMethod = document.querySelector(
                'input[name="payment-button"]:checked'
            );
            if (!selectedMethod) {
                showMessage(`${selectPaymethTextTranslate}`);
                return;
            }

            const methodId = selectedMethod.value;
            const WELLPAY_METHOD_ID = "1";

            if (methodId === WELLPAY_METHOD_ID) {
                try {
                    const response = await $.ajax({
                        url: window.App.routes.userWellpayBalance,
                        method: "GET",
                        dataType: "json",
                    });

                    if (response.wellpay !== undefined) {
                        const userWellpayBalance = parseFloat(response.wellpay);
                        if (isNaN(userWellpayBalance)) {
                            console.error(
                                "Wellpay balance received from server is not a valid number:",
                                response.wellpay
                            );
                            showMessage(
                                `${wellpayFormatErrorTextTranslate}`
                            );
                            return;
                        }

                        // Check if Wellpay balance is sufficient
                        if (userWellpayBalance < totalOrderPrice) {
                            const insufficientMessage =`<span style="color: red; font-weight: bold;">${insufficientWellpayBalanceTextTranslate}</span>`;
                            const balanceMessage =`${yourBalanceTextTranslate}`+ " " + formatRupiah(userWellpayBalance) +".";
                            const totalToPayMessage =`${amountPayTextTranslate}`+ " " +formatRupiah(totalOrderPrice) +".";

                            showMessage(
                                insufficientMessage +"<br><br>" +balanceMessage +"<br>" +totalToPayMessage
                            );
                            return;
                        }

                        showWellpayConfirmPopup(userWellpayBalance); // Ini akan menampilkan Stage 1
                    } else {
                        showMessage(
                            `${retrieveWellpayBalanceTextTranslate}`
                        );
                        console.error(
                            "Wellpay balance response format incorrect:",
                            response
                        );
                    }
                } catch (xhr) {
                    console.error("Failed to fetch Wellpay balance:", xhr);
                    showMessage(
                        `${retrieveCheckConnectionTextTranslate}`
                    );
                }
            } else if (selectedMethod.id === "qris") {
                showQrisPopup();
            } else {
                showGeneralConfirmPopup();
            }
        });
    }

    // Wellpay Confirmation Button (Mengelola Tahapan dan Submit Password)
    if (wellpayConfirmBtn) {
        wellpayConfirmBtn.addEventListener("click", async function () {
            if (currentWellpayPopupStage === "initial_confirm") {
                // Tahap 1: User mengkonfirmasi pembayaran Wellpay, pindah ke tahap input password
                currentWellpayPopupStage = "password_input";
                wellpayStage1.style.display = "none";
                wellpayStage2.style.display = "block";
                wellpayConfirmBtn.textContent = `${continueTextTranslate}`;

                // Bersihkan pesan error/status sebelumnya saat pindah tahap
                wellpayPasswordInput.value = "";
                wellpayPasswordError.style.display = "none";
                wellpayPopupMessage.style.display = "none";

                // Fokuskan ke input password
                wellpayPasswordInput.focus();

                // Set total yang akan dibayar di stage 2 (agar selalu up-to-date)
                wellpayAmountToPay.textContent = `${amountPayTextTranslate}: ${formatRupiah(
                    totalOrderPrice
                )}`;
            } else if (currentWellpayPopupStage === "password_input") {
                // Tahap 2: User submit password
                const password = wellpayPasswordInput.value;
                wellpayPasswordError.style.display = "none"; // Bersihkan error sebelumnya
                wellpayPopupMessage.style.display = "none"; // Bersihkan pesan umum sebelumnya

                if (password.trim() === "") {
                    wellpayPasswordError.textContent =
                        `${enterPassTextTranslate}`;
                    wellpayPasswordError.style.display = "block";
                    return; // Jangan lanjutkan jika password kosong
                }

                // Panggil fungsi pembayaran dengan password
                await processPaymentAjax(password);
            }
        });
    }

    // Wellpay Cancel Button (Reset Stage on Cancel)
    if (wellpayCancelBtn) {
        wellpayCancelBtn.addEventListener("click", function () {
            hideWellpayConfirmPopup(); // Ini akan mereset stage
            showMessage(`${wellpayCancelTextTranslate}`);
            if (wellpayRadio) wellpayRadio.checked = false; // Optionally uncheck Wellpay radio
        });
    }

    // Event listener untuk Enter di input password Wellpay
    if (wellpayPasswordInput) {
        wellpayPasswordInput.addEventListener("keypress", function (event) {
            if (event.key === "Enter" || event.keyCode === 13) {
                event.preventDefault(); // Mencegah form submit default
                wellpayConfirmBtn.click(); // Memicu klik tombol Confirm (yang sekarang jadi "Submit Payment")
            }
        });
    }

    // General Confirmation Popup buttons
    if (confirmBtn) {
        // This is the "Confirm Payment" button inside the general popup
        confirmBtn.addEventListener("click", function () {
            hideGeneralConfirmPopup();
            processPaymentAjax(); // Process payment after general confirmation
        });
    }

    // Success Popup buttons
    if (backHomeBtn) {
        backHomeBtn.addEventListener("click", function () {
            window.location.href = "/home"; // Adjust to your actual homepage route
        });
    }

    // QRIS Popup buttons
    if (doneBtn) {
        doneBtn.addEventListener("click", () => {
            hideQrisPopup();
            processPaymentAjax(); // Assuming QRIS payment is confirmed on 'Done'
            if (qrisRadio) qrisRadio.checked = false; // Uncheck QRIS radio button
        });
    }

    if (downloadQrisBtn && qrCodeImage) {
        downloadQrisBtn.addEventListener("click", () => {
            const link = document.createElement("a");
            link.href = qrCodeImage.src;
            link.download = "QRIS_Payment_Code.jpg";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }

    // Close QRIS popup when clicking outside content
    if (qrisPopup) {
        qrisPopup.addEventListener("click", function (event) {
            if (event.target === qrisPopup) {
                hideQrisPopup();
                if (qrisRadio) qrisRadio.checked = false;
            }
        });
    }

    // Inisialisasi toast (jika Anda memiliki toast di halaman utama)
    const toastElement = document.querySelector(".toast");
    if (toastElement) {
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
    }
});
