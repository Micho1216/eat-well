document.addEventListener('DOMContentLoaded', () => {
    const fileInput  = document.getElementById('profilePictureInput');
    const previewImg = document.getElementById('profilePicturePreview');
    const uploadBtn  = document.getElementById('profilePictureUploadBtn');

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

    const provinceSelect = document.getElementById('provinceSelect');
    const citySelect = document.getElementById('citySelect');
    const districtSelect  = document.getElementById('districtSelect');
    const villageSelect = document.getElementById('villageSelect');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function resetSelect(formSelect, placeholder)
    {
        formSelect.disabled = true;
        formSelect.innerHTML = '<option value="" selected>' + placeholder + '</option>';
    }

    function openSelect(formSelect)
    {
        formSelect.disabled = false;
    }

    (async () => {        
        resetSelect(provinceSelect, 'Provinsi');
        resetSelect(citySelect, 'Kota/Kabupaten');
        resetSelect(districtSelect, 'Kecamatan');
        resetSelect(villageSelect, 'Desa/Kelurahan');

        $.ajax({
            url: "api/fetch-provinces",
            type: "POST",
            dataType: 'JSON',
            success: function (result) {
                openSelect(provinceSelect);
                provinceSelect.innerHTML = '<option value="" selected>Provinsi</option>';
                $.each(result, function(key, value){
                    provinceSelect.insertAdjacentHTML('beforeend', '<option value="' + value.id + '">' + value.name + '</option>');
                })
            }
        });

    })();

    provinceSelect.addEventListener('change', async () => {
        var provinceId = provinceSelect.value;

        resetSelect(citySelect, 'Kota/Kabupaten');
        resetSelect(districtSelect, 'Kecamatan');
        resetSelect(villageSelect, 'Desa/Kelurahan');
        $.ajax({
            url: "api/fetch-cities",
            type: "POST",
            data: {
                province_id: provinceId,
            },
            dataType: 'JSON',
            success: function (result) {
                openSelect(citySelect);
                citySelect.innerHTML = '<option value="" selected>Kota/Kabupaten</option>';
                $.each(result, function(key, value){
                    citySelect.insertAdjacentHTML('beforeend','<option value="'+ value.id + '">' + value.name +  '</option>');
                });
            }
        });
    });

    citySelect.addEventListener('change', async () => {
        var cityId = citySelect.value;

        resetSelect(districtSelect, 'Kecamatan');
        resetSelect(villageSelect, 'Desa/Kelurahan');

        $.ajax({
            url: "api/fetch-districts",
            type: "POST",
            data: {
                city_id: cityId,
            },
            dataType: 'JSON',
            success: function (result) {
                openSelect(districtSelect);
                districtSelect.innerHTML = '<option value"" selected>Kecamatan</option>';
                $.each(result, function(key, value){
                    districtSelect.insertAdjacentHTML('beforeend',
                        '<option value="' + value.id + '">' + value.name + '</option>'
                    );
                });
            }
        });
    })

    districtSelect.addEventListener('change', async() => {
        var districtId = districtSelect.value; 

        resetSelect(villageSelect, 'Desa/Kelurahan');

        $.ajax({
            url: "api/fetch-villages",
            type: "POST",
            data: {
                district_id: districtId,
            },
            dataType: 'JSON',
            success: function (result) {
                openSelect(villageSelect);
                villageSelect.innerHTML = '<option value="" selected>Desa/Kelurahan</option>';
                $.each(result, function(key, value){
                    villageSelect.insertAdjacentHTML('beforeend',
                        '<option value="' + value.id + '">' + value.name + '</option>'
                    );
                })
            }

        })
    })
});
