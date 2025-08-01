document.addEventListener("DOMContentLoaded", function () {
    const topupInput = document.getElementById("topupAmount");
    const topupError = document.getElementById("topupError");

    // Dapatkan saldo awal dari DOM
    let currentBalance = parseFloat(
        document.getElementById("currentBalanceValue").value || 0
    );

    const maxBalance = 1000000000;
    const minTopup = 1000;
    const maxTopup = 20000000;

    // Custom Modal Elements
    const customModal1 = document.getElementById("customModal1");
    const customModal2 = document.getElementById("customModal2");
    const openCustomModal1Btn = document.getElementById("openCustomModal1");
    const closeCustomModal1Btn = document.getElementById("closeCustomModal1");
    const closeCustomModal2Btn = document.getElementById("closeCustomModal2");
    const nextCustomModalBtn = document.getElementById("nextCustomModalBtn");
    const backToCustomModal1Btn = document.getElementById("backToCustomModal1");
    const confirmTopupBtn = document.getElementById("confirmTopupBtn");

    // Elements for Modal 2 content
    const confirmTopupAmountSpan = document.getElementById("confirmTopupAmount");
    const confirmNewBalanceSpan = document.getElementById("confirmNewBalance");
    const finalTopupAmountInput = document.getElementById("finalTopupAmount");

    // Input password dan error message
    const accountPasswordInput = document.getElementById("accountPassword");
    const passwordError = document.getElementById("passwordError");

    // Success Toast Elements
    const successToast = document.getElementById("successToast");
    const successToastMessage = document.getElementById("successToastMessage");

    // Visibility balance
    const wellpayBalanceAmount = document.getElementById("wellpayBalanceAmount");
    const toggleVisibilityBtn = document.getElementById("toggleVisibilityBtn");
    const visibilityIcon = document.getElementById("visibilityIcon");

    // Ambil elemen untuk saldo saat ini di Modal 1
    const currentBalanceModal1 = customModal1.querySelector('.modal-body h5 span.fw-bold'); 

    const loadingOverlay = document.getElementById("loadingOverlay");

    let actualDisplayedBalance = wellpayBalanceAmount.textContent;
    const numericPart = actualDisplayedBalance.replace(/[^0-9]/g, "");
    const numberOfBullets = numericPart.length;
    const bulletString = "••••••";

    let isBalanceHidden = true;

    function showBalance(balanceValue) {
        const formattedBalance = parseFloat(balanceValue).toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        wellpayBalanceAmount.textContent = formattedBalance;
        wellpayBalanceAmount.classList.remove("bullet-display");
        visibilityIcon.textContent = "visibility_off";
        isBalanceHidden = false;
    }

    function hideBalance() {
        wellpayBalanceAmount.textContent = bulletString;
        wellpayBalanceAmount.classList.add("bullet-display");
        visibilityIcon.textContent = "visibility";
        isBalanceHidden = true;
    }

    if (isBalanceHidden) {
        hideBalance();
    } else {
        showBalance(currentBalance);
    }

    toggleVisibilityBtn.addEventListener("click", function () {
        if (isBalanceHidden) {
            showBalance(currentBalance);
        } else {
            hideBalance();
        }
    });

    // Function to show a custom modal
    function showModal(modalElement) {
        modalElement.style.display = "flex";
        document.body.style.overflow = "hidden";
    }

    // Function to hide a custom modal
    function hideModal(modalElement) {
        modalElement.style.display = "none";
        document.body.style.overflow = "";
    }

    // Function to show the success toast
    function showSuccessToast(message) {
        successToast.classList.remove("error");
        successToast.style.backgroundColor = "";
        successToastMessage.textContent = message;
        successToast.classList.add("show");
        setTimeout(() => {
            successToast.classList.remove("show");
        }, 3000);
    }

    // Function to show error toast
    function showErrorToast(message) {
        successToast.style.backgroundColor = "#dc3545"; // Warna merah untuk error
        successToastMessage.textContent = message;
        successToast.classList.add("show");
        setTimeout(() => {
            successToast.classList.remove("show");
            successToast.style.backgroundColor = "#4CAF50"; // Kembalikan ke warna sukses
        }, 4000);
    }

    // Function to format Rupiah input
    window.formatRupiah = function (input) {
        let value = input.value.replace(/\D/g, "");
        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        input.value = value;
    };

    // Event listener for input topupAmount
    if (topupInput) {
        topupInput.addEventListener("input", function (e) {
            formatRupiah(e.target);
            topupError.style.display = "none";
        });

        topupInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter' || event.keyCode === 13) {
                event.preventDefault(); // Mencegah perilaku default browser
                if (nextCustomModalBtn) {
                    nextCustomModalBtn.click();
                }
            }
        });
    }

    // Event listener to open Custom Modal 1
    if (openCustomModal1Btn) {

        var hasPassword = document.querySelector('meta[name="has-password"]')?.content;
        var pressedTopup = document.querySelector('meta[name="pressed-topup"]')?.content;

        if(pressedTopup == 1){
            showModal(customModal1);
            if (topupInput) {
                topupInput.value = "";
            }
            if (topupError) {
                topupError.style.display = "none";
            }
            if (currentBalanceModal1) {
                currentBalanceModal1.textContent = `Rp ${currentBalance.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }
        }

        openCustomModal1Btn.addEventListener("click", function () {
            if(hasPassword != 1)
            {
                let url = "/add-password"
                document.location.href = url;
            }
            else{
                showModal(customModal1);
                if (topupInput) {
                    topupInput.value = "";
                }
                if (topupError) {
                    topupError.style.display = "none";
                }
                if (currentBalanceModal1) {
                    currentBalanceModal1.textContent = `Rp ${currentBalance.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                }
            }
        });
    }

    // Event listener to close Custom Modal 1
    if (closeCustomModal1Btn) {
        closeCustomModal1Btn.addEventListener("click", function () {
            hideModal(customModal1);
        });
    }

    // Event listener to close Custom Modal 2
    if (closeCustomModal2Btn) {
        closeCustomModal2Btn.addEventListener("click", function () {
            hideModal(customModal2);
            accountPasswordInput.value = "";
            passwordError.style.display = "none";
        });
    }

    // Event listener for "Continue" button in Modal 1
    if (nextCustomModalBtn) {
        nextCustomModalBtn.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            let rawValue = topupInput.value.replace(/\./g, "").replace(/[^0-9]/g, "");
            let topupAmount = parseInt(rawValue);
            let valid = true;

            topupError.style.display = "none";
            topupError.innerText = "";

            if (isNaN(topupAmount) || topupAmount <= 0) {
                topupError.innerText = "Please enter a valid amount.";
                valid = false;
            } else if (topupAmount < minTopup) {
                topupError.innerText = `The minimum top-up amount is Rp ${minTopup.toLocaleString("id-ID")}.`;
                valid = false;
            } else if (topupAmount > maxTopup) {
                topupError.innerText = `The maximum top-up amount is Rp ${maxTopup.toLocaleString("id-ID")}.`;
                valid = false;
            } else if (topupAmount + currentBalance > maxBalance) {
                topupError.innerText = `Your balance cannot exceed Rp ${maxBalance.toLocaleString("id-ID")}.`;
                valid = false;
            }

            if (!valid) {
                topupError.style.display = "block";
                return;
            }

            hideModal(customModal1);
            showModal(customModal2);

            confirmTopupAmountSpan.textContent = `Rp ${topupAmount.toLocaleString("id-ID")}`;
            confirmNewBalanceSpan.textContent = `Rp ${(currentBalance + topupAmount).toLocaleString("id-ID")}`;
            finalTopupAmountInput.value = topupAmount;
            accountPasswordInput.value = "";
            passwordError.style.display = "none";
        });
    }

    // Event listener for "Back" button in Modal 2
    if (backToCustomModal1Btn) {
        backToCustomModal1Btn.addEventListener("click", function () {
            hideModal(customModal2);
            showModal(customModal1);
            accountPasswordInput.value = "";
            passwordError.style.display = "none";
        });
    }

    // Event listener for "Confirm" button in Modal 2 (final submission)
    if (confirmTopupBtn) {
        confirmTopupBtn.addEventListener("click", async function () {
            const finalAmount = finalTopupAmountInput.value;
            const password = accountPasswordInput.value;

            passwordError.style.display = "none";
            passwordError.innerText = "";

            if (password.trim() === "") {
                passwordError.innerText = "Please enter your password.";
                passwordError.style.display = "block";
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                console.error("CSRF token not found. Please add a meta tag: <meta name='csrf-token' content='{{ csrf_token() }}'>");
                showErrorToast("Application error: CSRF token missing.");
                return;
            }

            loadingOverlay.style.display = "flex";

            try {
                console.log(finalAmount);
                console.log(password);
                const response = await fetch('/topup', { // Pastikan URL ini sesuai dengan route Laravel Anda
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        amount: finalAmount,
                        password: password
                    })
                });

                const data = await response.json();

                loadingOverlay.style.display = "none";

                if (response.ok) { // Status kode 200-299
                    hideModal(customModal2);
                    showSuccessToast(data.message);
                    accountPasswordInput.value = "";

                    // Update currentBalance di JS
                    currentBalance = data.new_balance;

                    // Update tampilan saldo utama berdasarkan status visibilitas
                    if (isBalanceHidden) {
                        hideBalance(); // Jika tersembunyi, tetap sembunyikan dengan bullet
                    } else {
                        showBalance(currentBalance); // Jika terlihat, tampilkan saldo baru yang diformat
                    }

                } else { // Status kode 4xx, 5xx
                    if (data.errors && data.errors.password) {
                        passwordError.innerText = data.errors.password[0];
                        passwordError.style.display = "block";
                    } else if (data.message) {
                        showErrorToast(data.message);
                    } else {
                        showErrorToast("An unknown error occurred during top-up.");
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showErrorToast("Network error. Please try again.");
            }
        });
    }

    if (accountPasswordInput) {
        accountPasswordInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter' || event.keyCode === 13) {
                event.preventDefault(); // Mencegah submit form tradisional (jika ada)
                confirmTopupBtn.click(); // Memicu klik pada tombol konfirmasi
            }
        });
    }
});