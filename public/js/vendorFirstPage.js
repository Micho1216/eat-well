document.addEventListener('DOMContentLoaded', () => {
    const translationDataElement = document.getElementById("translation-data");
    const provinceTextTranslate = translationDataElement.dataset.provinceText;
    const cityTextTranslate = translationDataElement.dataset.cityText;
    const districtTextTranslate = translationDataElement.dataset.districtText;
    const villageTextTranslate = translationDataElement.dataset.villageText;
    
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
    
    /* ------------ Logo preview ------------- */
    const fileInput  = document.getElementById('vendorLogoInput');
    const previewImg = document.getElementById('vendorLogoPreview');
    const uploadBtn  = document.getElementById('logoUploadBtn');

    if (uploadBtn && fileInput) {
        uploadBtn.addEventListener('click', e => {
            e.preventDefault();
            fileInput.click();
        });
    }

    if (fileInput && previewImg) {
        fileInput.addEventListener('change', e => {
            const file = e.target.files?.[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = ev => (previewImg.src = ev.target.result);
            reader.readAsDataURL(file);
        });
    }

    /* ------------ Cascading selects ------------- */

    const provSel = document.getElementById('provinsi');
    const kotaSel = document.getElementById('kota');
    const kecSel  = document.getElementById('kecamatan');
    const kelSel  = document.getElementById('kelurahan');

    function resetSelect(sel, placeholder) {
        sel.disabled = true;
        sel.innerHTML = `<option value="" selected>${placeholder}</option>`;
        const hiddenInput = document.getElementById(`${sel.id}_name`);
        if (hiddenInput) {
            hiddenInput.value = '';
        }
    }
    function openSelect(sel) {
        sel.disabled = false;
    }

    $.ajax({
        url: "/api/fetch-provinces",
        type: "POST",
        dataType: 'JSON',
        success: function(result) {
            openSelect(provSel);
            provSel.innerHTML = `<option value="">${provinceTextTranslate}</option>`;
            $.each(result, function(key, value) {
                const selected = value.id.toString() === oldProv ? 'selected' : '';
                provSel.insertAdjacentHTML('beforeend', `<option value="${value.id}" ${selected}>${value.name}</option>`);
            });

            // If an old province value exists, trigger the change event to load subsequent dropdowns
            if (oldProv) {
                provSel.dispatchEvent(new Event('change'));
            }
        },
        error: function(xhr, status, error) {
            console.error("Error fetching provinces:", xhr, status, error);
            alert("Gagal memuat data provinsi. Silakan coba lagi.");
        }
    });
    provSel.addEventListener('change', async function() {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('provinsi_name').value = selectedOption.text;

        const provID = this.value;

        resetSelect(kotaSel, cityTextTranslate);
        resetSelect(kecSel, districtTextTranslate);
        resetSelect(kelSel, villageTextTranslate);

        if (provID) {
            $.ajax({
                url: "/api/fetch-cities",
                type: "POST",
                data: { province_id: provID },
                dataType: 'JSON',
                success: function(result) {
                    openSelect(kotaSel);
                    kotaSel.innerHTML = `<option value="">${cityTextTranslate}</option>`;
                    $.each(result, function(key, value) {
                        const selected = value.id.toString() === oldKota ? 'selected' : '';
                        kotaSel.insertAdjacentHTML('beforeend', `<option value="${value.id}" ${selected}>${value.name}</option>`);
                    });

                    // If an old city value exists, trigger the change event
                    if (oldKota) {
                        kotaSel.dispatchEvent(new Event('change'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching cities:", error);
                    alert("Gagal memuat data kota. Silakan coba lagi.");
                }
            });
        }
    });

    // --- Event listener for City change ---
    kotaSel.addEventListener('change', async function() {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('kota_name').value = selectedOption.text;

        const kotaID = this.value;

        resetSelect(kecSel, districtTextTranslate);
        resetSelect(kelSel, villageTextTranslate);

        if (kotaID) {
            $.ajax({
                url: "/api/fetch-districts",
                type: "POST",
                data: { city_id: kotaID },
                dataType: 'JSON',
                success: function(result) {
                    openSelect(kecSel);
                    kecSel.innerHTML = `<option value="">${districtTextTranslate}</option>`;
                    $.each(result, function(key, value) {
                        const selected = value.id.toString() === oldKec ? 'selected' : '';
                        kecSel.insertAdjacentHTML('beforeend', `<option value="${value.id}" ${selected}>${value.name}</option>`);
                    });

                    // If an old district value exists, trigger the change event
                    if (oldKec) {
                        kecSel.dispatchEvent(new Event('change'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching districts:", error);
                    alert("Gagal memuat data kecamatan. Silakan coba lagi.");
                }
            });
        }
    });

    // --- Event listener for District change ---
    kecSel.addEventListener('change', async function() {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('kecamatan_name').value = selectedOption.text;

        const kecID = this.value;

        resetSelect(kelSel, villageTextTranslate);

        if (kecID) {
            $.ajax({
                url: "/api/fetch-villages",
                type: "POST",
                data: { district_id: kecID },
                dataType: 'JSON',
                success: function(result) {
                    openSelect(kelSel);
                    kelSel.innerHTML = `<option value="">${villageTextTranslate}</option>`;
                    $.each(result, function(key, value) {
                        const selected = value.id.toString() === oldKel ? 'selected' : '';
                        kelSel.insertAdjacentHTML('beforeend', `<option value="${value.id}" ${selected}>${value.name}</option>`);
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching villages:", error);
                    alert("Gagal memuat data kelurahan. Silakan coba lagi.");
                }
            });
        }
    });

    kelSel.addEventListener('change', async function() {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('kelurahan_name').value = selectedOption.text;
    });
});
