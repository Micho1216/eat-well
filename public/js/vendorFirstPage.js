document.addEventListener('DOMContentLoaded', () => {
    const translationDataElement = document.getElementById("translation-data");
    const provinceTextTranslate = translationDataElement.dataset.provinceText;
    const cityTextTranslate = translationDataElement.dataset.cityText;
    const districtTextTranslate = translationDataElement.dataset.districtText;
    const villageTextTranslate = translationDataElement.dataset.villageText;

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
    const API_KEY = '543d80b3b490190006f5a670ce47292b0ebe9a3da6a097a0efc32b87096de8e4';

    const provSel = document.getElementById('provinsi');
    const kotaSel = document.getElementById('kota');
    const kecSel  = document.getElementById('kecamatan');
    const kelSel  = document.getElementById('kelurahan');

    async function fetchData(url) {
        const res = await fetch(url);
        if (!res.ok) throw new Error('Network error');
        const json = await res.json();
        return json.value || [];
    }

    function resetSelect(sel, placeholder) {
        sel.innerHTML = `<option value="">${placeholder}</option>`;
    }

    /* ---- 1. Load provinces on page load ---- */
    (async () => {
        resetSelect(provSel, `${provinceTextTranslate}`);
        const provs = await fetchData(`https://api.binderbyte.com/wilayah/provinsi?api_key=${API_KEY}`);
        provs.forEach(p => {
            const selected = p.id === oldProv ? 'selected' : '';
            provSel.insertAdjacentHTML('beforeend',
                `<option value="${p.id}" ${selected}>${p.name}</option>`);
        });

        if (oldProv) await loadKota(oldProv);
    })().catch(console.error);

    /* ---- 2. Province → cities ---- */
    provSel.addEventListener('change', async () => {
        resetSelect(kotaSel, `${cityTextTranslate}`);
        resetSelect(kecSel, `${districtTextTranslate}`);
        resetSelect(kelSel, `${villageTextTranslate}`);
        if (!provSel.value) return;
        await loadKota(provSel.value);
    });

    async function loadKota(provId) {
        resetSelect(kotaSel, `${cityTextTranslate}`);
        const cities = await fetchData(`https://api.binderbyte.com/wilayah/kabupaten?api_key=${API_KEY}&id_provinsi=${provId}`);
        cities.forEach(c => {
            const selected = c.id === oldKota ? 'selected' : '';
            kotaSel.insertAdjacentHTML('beforeend',
                `<option value="${c.id}" ${selected}>${c.name}</option>`);
        });

        if (oldKota) await loadKec(oldKota);
    }

    /* ---- 3. City → districts ---- */
    kotaSel.addEventListener('change', async () => {
        resetSelect(kecSel, `${districtTextTranslate}`);
        resetSelect(kelSel, `${villageTextTranslate}`);
        if (!kotaSel.value) return;
        await loadKec(kotaSel.value);
    });

    async function loadKec(kotaId) {
        resetSelect(kecSel, `${districtTextTranslate}`);
        const dists = await fetchData(`https://api.binderbyte.com/wilayah/kecamatan?api_key=${API_KEY}&id_kabupaten=${kotaId}`);
        dists.forEach(d => {
            const selected = d.id === oldKec ? 'selected' : '';
            kecSel.insertAdjacentHTML('beforeend',
                `<option value="${d.id}" ${selected}>${d.name}</option>`);
        });

        if (oldKec) await loadKel(oldKec);
    }

    /* ---- 4. District → villages ---- */
    kecSel.addEventListener('change', async () => {
        resetSelect(kelSel, `${villageTextTranslate}`);
        if (!kecSel.value) return;
        await loadKel(kecSel.value);
    });

    async function loadKel(kecId) {
        resetSelect(kelSel, `${villageTextTranslate}`);
        const vills = await fetchData(`https://api.binderbyte.com/wilayah/kelurahan?api_key=${API_KEY}&id_kecamatan=${kecId}`);
        vills.forEach(v => {
            const selected = v.id === oldKel ? 'selected' : '';
            kelSel.insertAdjacentHTML('beforeend',
                `<option value="${v.id}" ${selected}>${v.name}</option>`);
        });
    }
});
