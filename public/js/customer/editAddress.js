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

    // Mengambil variabel global dari Blade
    const currentAddress = window.currentAddress || {};
    const oldInput = window.oldInput || {};

    function hasOldDropdownInput() {
        return (oldInput.provinsi_id && oldInput.provinsi_id !== "null" && oldInput.provinsi_id !== "") ||
               (oldInput.kota_id && oldInput.kota_id !== "null" && oldInput.kota_id !== "") ||
               (oldInput.kecamatan_id && oldInput.kecamatan_id !== "null" && oldInput.kecamatan_id !== "") ||
               (oldInput.kelurahan_id && oldInput.kelurahan_id !== "null" && oldInput.kelurahan_id !== "");
    }

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

    // --- Fungsi cleanRegionName yang disesuaikan ---
    // Hanya konversi ke huruf kecil dan trim whitespace
    function cleanRegionName(name) {
        if (!name) return '';
        return name.toLowerCase().trim();
    }

    async function populateAndSelectDropdowns() {
        // Tentukan data mana yang akan digunakan untuk pre-fill: oldInput jika ada, else currentAddress
        const dataToUse = hasOldDropdownInput() ? oldInput : currentAddress;

        // 1. Load dan Pilih Provinsi
        const provData = await $.ajax({
            url: "/api/fetch-provinces",
            type: "POST",
            dataType: 'JSON'
        });

        provinsiSelect.innerHTML = '<option value="">Pilih Provinsi</option>';
        provData.forEach(prov => {
            provinsiSelect.insertAdjacentHTML('beforeend', `<option value="${prov.id}">${prov.name}</option>`);
        });

        let selectedProvId = '';
        // Prioritaskan ID untuk pemilihan
        if (dataToUse.provinsi_id && dataToUse.provinsi_id !== "null" && dataToUse.provinsi_id !== "") {
            selectedProvId = dataToUse.provinsi_id;
            provinsiSelect.value = selectedProvId;
        } else if (dataToUse.provinsi || dataToUse.provinsi_name) { // Fallback ke nama jika ID tidak ada/valid
            const nameToMatch = cleanRegionName(dataToUse.provinsi_name || dataToUse.provinsi);
            for (let i = 0; i < provinsiSelect.options.length; i++) {
                const optionText = cleanRegionName(provinsiSelect.options[i].text);
                if (optionText === nameToMatch) {
                    selectedProvId = provinsiSelect.options[i].value;
                    provinsiSelect.value = selectedProvId;
                    break;
                }
            }
        }
        document.getElementById('provinsi_name').value = provinsiSelect.selectedIndex !== -1 ? provinsiSelect.options[provinsiSelect.selectedIndex].text : '';


        // 2. Load dan Pilih Kota (Hanya jika Provinsi terpilih/cocok)
        let selectedKotaId = '';
        if (selectedProvId) {
            openSelect(kotaSelect);
            const kotaData = await $.ajax({
                url: "/api/fetch-cities",
                type: "POST",
                data: { province_id: selectedProvId },
                dataType: 'JSON'
            });

            kotaSelect.innerHTML = '<option value="">Pilih Kota</option>';
            kotaData.forEach(kota => {
                kotaSelect.insertAdjacentHTML('beforeend', `<option value="${kota.id}">${kota.name}</option>`);
            });

            if (dataToUse.kota_id && dataToUse.kota_id !== "null" && dataToUse.kota_id !== "") {
                selectedKotaId = dataToUse.kota_id;
                kotaSelect.value = selectedKotaId;
            } else if (dataToUse.kota || dataToUse.kota_name) {
                const nameToMatch = cleanRegionName(dataToUse.kota_name || dataToUse.kota);
                for (let i = 0; i < kotaSelect.options.length; i++) {
                    const optionText = cleanRegionName(kotaSelect.options[i].text);
                    if (optionText === nameToMatch) {
                        selectedKotaId = kotaSelect.options[i].value;
                        kotaSelect.value = selectedKotaId;
                        break;
                    }
                }
            }
            document.getElementById('kota_name').value = kotaSelect.selectedIndex !== -1 ? kotaSelect.options[kotaSelect.selectedIndex].text : '';
        } else {
            resetSelect(kotaSelect, 'Pilih Kota');
        }

        // 3. Load dan Pilih Kecamatan (Hanya jika Kota terpilih/cocok)
        let selectedKecId = '';
        if (selectedKotaId) {
            openSelect(kecamatanSelect);
            const kecData = await $.ajax({
                url: "/api/fetch-districts",
                type: "POST",
                data: { city_id: selectedKotaId },
                dataType: 'JSON'
            });

            kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            kecData.forEach(kec => {
                kecamatanSelect.insertAdjacentHTML('beforeend', `<option value="${kec.id}">${kec.name}</option>`);
            });

            if (dataToUse.kecamatan_id && dataToUse.kecamatan_id !== "null" && dataToUse.kecamatan_id !== "") {
                selectedKecId = dataToUse.kecamatan_id;
                kecamatanSelect.value = selectedKecId;
            } else if (dataToUse.kecamatan || dataToUse.kecamatan_name) {
                const nameToMatch = cleanRegionName(dataToUse.kecamatan_name || dataToUse.kecamatan);
                for (let i = 0; i < kecamatanSelect.options.length; i++) {
                    const optionText = cleanRegionName(kecamatanSelect.options[i].text);
                    if (optionText === nameToMatch) {
                        selectedKecId = kecamatanSelect.options[i].value;
                        kecamatanSelect.value = selectedKecId;
                        break;
                    }
                }
            }
            document.getElementById('kecamatan_name').value = kecamatanSelect.selectedIndex !== -1 ? kecamatanSelect.options[kecamatanSelect.selectedIndex].text : '';
        } else {
            resetSelect(kecamatanSelect, 'Pilih Kecamatan');
        }

        // 4. Load dan Pilih Kelurahan (Hanya jika Kecamatan terpilih/cocok)
        let selectedKelId = '';
        if (selectedKecId) {
            openSelect(kelurahanSelect);
            const kelData = await $.ajax({
                url: "/api/fetch-villages",
                type: "POST",
                data: { district_id: selectedKecId },
                dataType: 'JSON'
            });

            kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan</option>';
            kelData.forEach(kel => {
                kelurahanSelect.insertAdjacentHTML('beforeend', `<option value="${kel.id}">${kel.name}</option>`);
            });

            if (dataToUse.kelurahan_id && dataToUse.kelurahan_id !== "null" && dataToUse.kelurahan_id !== "") {
                selectedKelId = dataToUse.kelurahan_id;
                kelurahanSelect.value = selectedKelId;
            } else if (dataToUse.kelurahan || dataToUse.kelurahan_name) {
                const nameToMatch = cleanRegionName(dataToUse.kelurahan_name || dataToUse.kelurahan);
                for (let i = 0; i < kelurahanSelect.options.length; i++) {
                    const optionText = cleanRegionName(kelurahanSelect.options[i].text);
                    if (optionText === nameToMatch) {
                        selectedKelId = kelurahanSelect.options[i].value;
                        kelurahanSelect.value = selectedKelId;
                        break;
                    }
                }
            }
            document.getElementById('kelurahan_name').value = kelurahanSelect.selectedIndex !== -1 ? kelurahanSelect.options[kelurahanSelect.selectedIndex].text : '';
        } else {
            resetSelect(kelurahanSelect, 'Pilih Kelurahan');
        }

        // Jika old input digunakan (misal: ada error validasi di field dropdown)
        if (hasOldDropdownInput()) {
            // Ini untuk menampilkan pesan validasi jika ada oldInput dan validasi gagal
            const dropdowns = [provinsiSelect, kotaSelect, kecamatanSelect, kelurahanSelect];
            dropdowns.forEach(dropdown => {
                if (!dropdown.validity.valid) { // Jika elemen dropdown tidak valid
                    dropdown.classList.add('is-invalid');
                    const feedbackElement = dropdown.nextElementSibling;
                    if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
                        setCustomValidationMessage(dropdown, feedbackElement);
                    }
                }
            });
        }
    }

    populateAndSelectDropdowns();

    // Event listeners untuk pemuatan dinamis setelah pemuatan halaman awal
    // ... (kode event listener change seperti sebelumnya, tidak ada perubahan) ...
    provinsiSelect.addEventListener('change', async function() {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('provinsi_name').value = selectedOption.text;

        const provID = this.value;

        resetSelect(kotaSelect, 'Pilih Kota');
        resetSelect(kecamatanSelect, 'Pilih Kecamatan');
        resetSelect(kelurahanSelect, 'Pilih Kelurahan');

        if (provID) {
            await $.ajax({
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

    kotaSelect.addEventListener('change', async function() {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('kota_name').value = selectedOption.text;

        const kotaID = this.value;

        resetSelect(kecamatanSelect, 'Pilih Kecamatan');
        resetSelect(kelurahanSelect, 'Pilih Kelurahan');

        if (kotaID) {
            await $.ajax({
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

    kecamatanSelect.addEventListener('change', async function() {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('kecamatan_name').value = selectedOption.text;

        const kecID = this.value;

        resetSelect(kelurahanSelect, 'Pilih Kelurahan');

        if (kecID) {
            await $.ajax({
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

    kelurahanSelect.addEventListener('change', async function() {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('kelurahan_name').value = selectedOption.text;
        this.dispatchEvent(new Event('input'));
    });
});