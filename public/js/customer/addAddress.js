document.addEventListener('DOMContentLoaded', () => {
    // Set up CSRF token for all AJAX requests using jQuery
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    if (csrfToken) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
    } else {
        console.error('CSRF token not found! Please ensure <meta name="csrf-token" content="..."> is in your head.');
    }

    // --- Fungsi untuk menangani validasi kustom dan pesan error ---
    function setCustomValidationMessage(inputElement, feedbackElement) {
        if (inputElement.validity.valueMissing) {
            feedbackElement.textContent = feedbackElement.dataset.messageRequired || 'Bidang ini tidak boleh kosong.';
        } else if (inputElement.validity.patternMismatch) {
            feedbackElement.textContent = feedbackElement.dataset.messagePattern || 'Format tidak sesuai.';
        } else if (inputElement.validity.tooShort) {
            feedbackElement.textContent = feedbackElement.dataset.messageMinlength || `Minimal ${inputElement.minLength} karakter.`;
        } else if (inputElement.validity.tooLong) {
            feedbackElement.textContent = feedbackElement.dataset.messageMaxlength || `Maksimal ${inputElement.maxLength} karakter.`;
        } else {
            feedbackElement.textContent = ''; // Hapus pesan jika valid
        }
    }

    // --- Inisialisasi Validasi Bootstrap ---
    (function() {
        'use strict';
        const form = document.getElementById('addressForm');

        document.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('input', function() {
                const feedbackElement = this.nextElementSibling;
                if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
                    if (!this.validity.valid && this.type !== 'hidden') {
                        this.classList.add('is-invalid');
                        setCustomValidationMessage(this, feedbackElement);
                    } else {
                        this.classList.remove('is-invalid');
                        feedbackElement.textContent = '';
                    }
                }
            });

            if (input.tagName === 'SELECT') {
                input.addEventListener('change', function() {
                    const feedbackElement = this.nextElementSibling;
                    if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
                        if (!this.validity.valid) {
                            this.classList.add('is-invalid');
                            setCustomValidationMessage(this, feedbackElement);
                        } else {
                            this.classList.remove('is-invalid');
                            feedbackElement.textContent = '';
                        }
                    }
                });
            }
        });

        form.addEventListener('submit', function(event) {
            let formValid = true;

            document.querySelectorAll('#addressForm [required], #addressForm [pattern], #addressForm [minlength], #addressForm [maxlength]').forEach(inputElement => {
                if (!inputElement.checkValidity()) {
                    inputElement.classList.add('is-invalid');
                    const feedbackElement = inputElement.nextElementSibling;
                    if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
                        setCustomValidationMessage(inputElement, feedbackElement);
                    }
                    formValid = false;
                } else {
                    inputElement.classList.remove('is-invalid');
                    const feedbackElement = inputElement.nextElementSibling;
                    if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
                        feedbackElement.textContent = '';
                    }
                }
            });

            if (!formValid) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);
    })();

    // --- Logika Pengisian Dropdown dan Input Hidden (Menggunakan AJAX ke Laravel) ---

    const provinsiSelect = document.getElementById('provinsi');
    const kotaSelect = document.getElementById('kota');
    const kecamatanSelect = document.getElementById('kecamatan');
    const kelurahanSelect = document.getElementById('kelurahan');

    function resetSelect(formSelect, placeholder) {
        formSelect.disabled = true;
        formSelect.innerHTML = '<option value="" selected>' + placeholder + '</option>';
        const hiddenInput = document.getElementById(formSelect.id + '_name');
        if (hiddenInput) {
            hiddenInput.value = '';
        }
        formSelect.classList.remove('is-invalid');
        const feedbackElement = formSelect.nextElementSibling;
        if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
            feedbackElement.textContent = '';
        }
    }

    function openSelect(formSelect) {
        formSelect.disabled = false;
    }

    // Load Provinsi saat halaman dimuat
    resetSelect(provinsiSelect, 'Pilih Provinsi');
    resetSelect(kotaSelect, 'Pilih Kota');
    resetSelect(kecamatanSelect, 'Pilih Kecamatan');
    resetSelect(kelurahanSelect, 'Pilih Kelurahan');

    $.ajax({
        url: "/api/fetch-provinces",
        type: "POST",
        dataType: 'JSON',
        success: function(result) {
            openSelect(provinsiSelect);
            provinsiSelect.innerHTML = '<option value="">Pilih Provinsi</option>';
            $.each(result, function(key, value) {
                provinsiSelect.insertAdjacentHTML('beforeend', '<option value="' + value.id + '">' + value.name + '</option>');
            });
        },
        error: function(xhr, status, error) {
            let errorMessage = "Gagal memuat data provinsi. Silakan coba lagi.";
            if (xhr.status) {
                errorMessage += ` Status: ${xhr.status} ${xhr.statusText}.`;
            }
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage += ` Pesan: ${xhr.responseJSON.message}`;
            } else if (xhr.responseText) {
                errorMessage += ` Respons: ${xhr.responseText.substring(0, 100)}...`;
            }
            console.error("Error fetching provinces:", xhr, status, error);
            alert(errorMessage);
        }
    });

    // Event listener untuk Provinsi
    provinsiSelect.addEventListener('change', async function() {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('provinsi_name').value = selectedOption.text;

        const provID = this.value;

        resetSelect(kotaSelect, 'Pilih Kota');
        resetSelect(kecamatanSelect, 'Pilih Kecamatan');
        resetSelect(kelurahanSelect, 'Pilih Kelurahan');

        if (provID) {
            $.ajax({
                url: "/api/fetch-cities",
                type: "POST",
                data: { province_id: provID },
                dataType: 'JSON',
                success: function(result) {
                    openSelect(kotaSelect);
                    kotaSelect.innerHTML = '<option value="">Pilih Kota</option>';
                    $.each(result, function(key, value) {
                        kotaSelect.insertAdjacentHTML('beforeend', '<option value="' + value.id + '">' + value.name + '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching cities:", error);
                    alert("Gagal memuat data kota. Silakan coba lagi.");
                }
            });
        } else {
            this.classList.add('is-invalid');
            this.nextElementSibling.textContent = this.nextElementSibling.dataset.messageRequired;
        }
        this.dispatchEvent(new Event('input'));
    });

    // Event listener untuk Kota
    kotaSelect.addEventListener('change', async function() {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('kota_name').value = selectedOption.text;

        const kotaID = this.value;

        resetSelect(kecamatanSelect, 'Pilih Kecamatan');
        resetSelect(kelurahanSelect, 'Pilih Kelurahan');

        if (kotaID) {
            $.ajax({
                url: "/api/fetch-districts",
                type: "POST",
                data: { city_id: kotaID },
                dataType: 'JSON',
                success: function(result) {
                    openSelect(kecamatanSelect);
                    kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
                    $.each(result, function(key, value) {
                        kecamatanSelect.insertAdjacentHTML('beforeend', '<option value="' + value.id + '">' + value.name + '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching districts:", error);
                    alert("Gagal memuat data kecamatan. Silakan coba lagi.");
                }
            });
        } else {
            this.classList.add('is-invalid');
            this.nextElementSibling.textContent = this.nextElementSibling.dataset.messageRequired;
        }
        this.dispatchEvent(new Event('input'));
    });

    // Event listener untuk Kecamatan
    kecamatanSelect.addEventListener('change', async function() {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('kecamatan_name').value = selectedOption.text;

        const kecID = this.value;

        resetSelect(kelurahanSelect, 'Pilih Kelurahan');

        if (kecID) {
            $.ajax({
                url: "/api/fetch-villages",
                type: "POST",
                data: { district_id: kecID },
                dataType: 'JSON',
                success: function(result) {
                    openSelect(kelurahanSelect);
                    kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan</option>';
                    $.each(result, function(key, value) {
                        kelurahanSelect.insertAdjacentHTML('beforeend', '<option value="' + value.id + '">' + value.name + '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching villages:", error);
                    alert("Gagal memuat data kelurahan. Silakan coba lagi.");
                }
            });
        } else {
            this.classList.add('is-invalid');
            this.nextElementSibling.textContent = this.nextElementSibling.dataset.messageRequired;
        }
        this.dispatchEvent(new Event('input'));
    });

    // Event listener untuk Kelurahan
    kelurahanSelect.addEventListener('change', async function() {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('kelurahan_name').value = selectedOption.text;
        this.dispatchEvent(new Event('input'));
    });
});