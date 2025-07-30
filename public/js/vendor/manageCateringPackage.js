// File: importPackage.js

// Fungsi untuk mengunduh template CSV
function downloadTemplateCSV() {
    const link = document.createElement("a");
    link.href = "/asset/catering/homePage/template_package_import.csv";
    link.download = "template_package_import.csv";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Tampilkan pesan sukses
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: window.locale.success,
        text: message,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'OK'
    });
}

// Tampilkan pesan error
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: window.locale.failed,
        text: message,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Coba Lagi'
    });
}

// Listener setelah DOM siap
document.addEventListener('DOMContentLoaded', function () {
    const uploadInput = document.getElementById('import');
    if (!uploadInput) return;

    uploadInput.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = async (evt) => {
            const workbook = XLSX.read(new Uint8Array(evt.target.result), { type: 'array' });
            const rows = XLSX.utils.sheet_to_json(workbook.Sheets[workbook.SheetNames[0]], {
                defval: ''
            });

            if (!rows.length) {
                showError(window.locale.empty_file);
                return;
            }

            const requiredFields = ['name', 'categoryId'];
            const hasRequiredColumns = requiredFields.every(field => field in rows[0]);

            if (!hasRequiredColumns) {
                showError(window.locale.wrong_column_format);
                return;
            }

            const postUrl = '/manageCateringPackage';
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            const requests = rows.map(async row => {
                const fd = new FormData();
                fd.append('_token', csrf);
                fd.append('name', row['name']);
                fd.append('categoryId', row['categoryId']);
                fd.append('averageCalories', row['averageCalories']);
                fd.append('breakfastPrice', row['breakfastPrice']);
                fd.append('lunchPrice', row['lunchPrice']);
                fd.append('dinnerPrice', row['dinnerPrice']);

                try {
                    const res = await fetch(postUrl, {
                        method: 'POST',
                        body: fd,
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    return { success: res.ok };
                } catch (err) {
                    console.error('Request failed:', err);
                    return { success: false };
                }
            });

            try {
                const results = await Promise.all(requests);
                const ok = results.filter(r => r.success).length;
                showSuccess(
                    `${window.locale.import_success} ${ok}, ${window.locale.import_failed} ${results.length - ok}`
                );
                setTimeout(() => location.reload(), 1500);
            } catch (err) {
                console.error(err);
                showError(window.locale.import_error);
            }
        };

        reader.readAsArrayBuffer(file);
    });
});




function handleEditClick(btn) {
    const data = JSON.parse(btn.dataset.package);
    console.log('DATA PACKAGE:', data);
    openEditModal(data);
}

function openEditModal(data) {
    document.getElementById('packageModalLabel').innerText = window.locale.edit_package;

    // Isi form
    document.getElementById('packageName').value = data.name;
    document.getElementById('category').value = data.categoryId;
    document.getElementById('breakfastPrice').value = (+data.breakfastPrice).toFixed(2);
    document.getElementById('lunchPrice').value = (+data.lunchPrice).toFixed(2);
    document.getElementById('dinnerPrice').value = (+data.dinnerPrice).toFixed(2);
    document.getElementById('averageCalories').value = data.averageCalories;

    // Cuisine
    const cuisineInputs = document.getElementById('cuisineInputs');
    cuisineInputs.innerHTML = '';
    document.querySelectorAll('#cuisine-buttons button').forEach(b => {
        b.classList.replace('btn-success', 'btn-outline-secondary');
    });
    data.cuisines.forEach(id => {
        const btn = document.querySelector(`#cuisine-buttons button[onclick*="${id}"]`);
        if (btn) {
            btn.classList.replace('btn-outline-secondary', 'btn-success');
        }
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'cuisine_types[]';
        hidden.value = id;
        cuisineInputs.appendChild(hidden);
    });

    // Reset Dropzone file lama
    menuDropzone.removeAllFiles(true);
    imageDropzone.removeAllFiles(true);

    // Tambahkan preview file lama
    if (data.menuPDFPath) {
        let mockFile = {
            name: data.menuPDFPath,
            size: 123456
        };
        menuDropzone.emit("addedfile", mockFile);
        menuDropzone.emit("complete", mockFile);
    }

    if (data.imgPath) {
        let mockFile = {
            name: data.imgPath,
            size: 123456
        };
        imageDropzone.emit("addedfile", mockFile);
        imageDropzone.emit("thumbnail", mockFile, `/asset/menus/${data.imgPath}`);
        imageDropzone.emit("complete", mockFile);
    }

    // Update form action
    const form = document.getElementById('packageForm');
    form.action = `/packages/${data.id}`;
    form.setAttribute('data-method', 'PUT');

    // Tambah _method hidden input (PUT)
    form.querySelectorAll('input[name="_method"]').forEach(el => el.remove());
    const spoof = document.createElement('input');
    spoof.type = 'hidden';
    spoof.name = '_method';
    spoof.value = 'PUT';
    form.appendChild(spoof);

    new bootstrap.Modal(document.getElementById('packageModal')).show();
}

document.getElementById('excelUpload').addEventListener('change', function (e) {
    const file = e.target.files[0];
    const reader = new FileReader();

    reader.onload = function (e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: 'array' });
        const sheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[sheetName];
        const jsonData = XLSX.utils.sheet_to_json(worksheet);

        const firstRow = jsonData[0];

        if (firstRow) {
            document.getElementById('packageName').value = firstRow["name"];
            document.getElementById('category').value = firstRow["categoryId"];
            document.getElementById('breakfastPrice').value = firstRow["breakfastPrice"];
            document.getElementById('lunchPrice').value = firstRow["lunchPrice"];
            document.getElementById('dinnerPrice').value = firstRow["dinnerPrice"];
            document.getElementById('averageCalories').value = firstRow["averageCalories"];
        }
    };

    reader.readAsArrayBuffer(file);
});

function mapCategory(name) {
    const categoryMap = {
        "Vegetarian": 1,
        "Gluten-Free": 2,
        "Halal": 3,
        "Low Carb": 4,
        "Low Calorie": 5,
        "Organic": 6,
    };
    return categoryMap[name] || '';
}

// function mapCuisineNames(cuisineStr) {
//     const cuisineMap = {
//         "Indonesian": 1,
//         "Chinese": 2,
//         "Japanese": 3,
//         "Korean": 4,
//         "Western": 5,
//         "Fusion": 6
//     };
//     const names = cuisineStr.split(',').map(n => n.trim());
//     return names.map(n => cuisineMap[n]).filter(Boolean);
// }

// function toggleCuisine(id, event = null) {
//     const existingInput = document.getElementById(`cuisine-${id}`);
//     if (!existingInput) {
//         const input = document.createElement('input');
//         input.type = 'hidden';
//         input.name = 'cuisineIds[]';
//         input.value = id;
//         input.id = `cuisine-${id}`;
//         document.getElementById('cuisineInputs').appendChild(input);
//     }

//     if (event) {
//         event.target.classList.toggle('btn-outline-secondary');
//         event.target.classList.toggle('btn-success');
//     }
// }

function showConfirm(message) {
    return Swal.fire({
        title: window.locale.confirm,
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    });
}

function deletePackage(id) {
    showConfirm(window.locale.confirm_delete_msg).then((result) => {
        if (result.isConfirmed) {
            fetch(`/packages/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess(window.locale.delete_success);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showError(window.locale.delete_failed);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError(window.locale.delete_error);
                });
        }
    });
}

const selectedCuisineIds = new Set();

function toggleCuisine(id, event) {
    const inputContainer = document.getElementById('cuisineInputs');
    const button = event.target;

    if (selectedCuisineIds.has(id)) {
        selectedCuisineIds.delete(id);
        document.getElementById('cuisine-input-' + id).remove();
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    } else {
        selectedCuisineIds.add(id);
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'cuisine_types[]';
        input.value = id;
        input.id = 'cuisine-input-' + id;
        inputContainer.appendChild(input);

        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-success');
    }
}

function openAddModal() {
    document.getElementById('packageModalLabel').innerText = window.locale.add_package;
    document.getElementById('packageForm').reset();

    selectedCuisineIds.clear();
    document.getElementById('cuisineInputs').innerHTML = '';
    document.querySelectorAll('#cuisine-buttons button').forEach(btn => {
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-secondary');
    });
}

Dropzone.autoDiscover = false;

/* PDF */
var menuDropzone = new Dropzone("#menuDropzone", {
    url: "#",
    autoProcessQueue: false,
    maxFiles: 1,
    paramName: "menuPDFPath",
    acceptedFiles: ".pdf",
    addRemoveLinks: true,
    dictRemoveFile: window.locale.dz_change_file,
    dictDefaultMessage: window.locale.dz_drop_files_here,
});

/* Image */
var imageDropzone = new Dropzone("#imageDropzone", {
    url: "#",
    autoProcessQueue: false,
    maxFiles: 1,
    paramName: "imgPath",
    acceptedFiles: ".png,.jpg,.jpeg",
    maxFilesize: 10,
    addRemoveLinks: true,
    dictRemoveFile: window.locale.dz_change_image,
    dictDefaultMessage: window.locale.dz_drop_image,
});

document.getElementById("packageForm").addEventListener("submit", function (e) {
    e.preventDefault();
    e.stopPropagation();

    const form = e.target;
    const formData = new FormData(form);

    if (menuDropzone.getAcceptedFiles().length > 0) {
        formData.append("menuPDFPath", menuDropzone.getAcceptedFiles()[0]);
    }

    if (imageDropzone.getAcceptedFiles().length > 0) {
        formData.append("imgPath", imageDropzone.getAcceptedFiles()[0]);
    }

    const isEdit = form.getAttribute('data-method') === 'PUT';
    const url = form.action;
    const method = 'POST';

    fetch(url, {
        method: method,
        headers: {
            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
        },
        body: formData
    })
        .then(res => {
            if (!res.ok) throw new Error("Gagal simpan");
            return res.text();
        })
        .then(response => {
            console.log("Sukses:", response);
            window.location.href = "/manageCateringPackage";
        })
        .catch(err => {
            console.error("Gagal:", err);
            alert("Ada error waktu simpan paket");
        });
});

const VENDOR_ID = document.getElementById('vendorId').value;
const carousel = document.getElementById("carousel-wrapper");
const imageInput = document.getElementById("imageInput");
const MAX_IMAGES = 5;

function loadPreviews() {
    fetch(`/vendor-previews?vendorId=${VENDOR_ID}`)
        .then(res => res.json())
        .then(data => {
            carousel.innerHTML = '';
            data.previews.forEach(pv => {
                const item = createImageItem('/' + pv.previewPicturePath, pv.vendorPreviewId);
                carousel.appendChild(item);
            });
            renderAddButton();
        });
}

function createImageItem(src, previewId) {
    const item = document.createElement("div");
    item.className = "carousel-item";
    const img = document.createElement("img");
    img.src = src;

    const button = document.createElement("button");
    button.className = "remove-button";
    button.dataset.previewId = previewId;

    updateButtonText(button);

    button.addEventListener("click", () => {
        const total = carousel.querySelectorAll(".carousel-item").length;
        if (total <= 3) {
            imageInput.dataset.replaceTarget = src;
            imageInput.dataset.replaceId = previewId;
            imageInput.click();
        } else {
            fetch(`/vendor-previews/${previewId}`, {
                method: "DELETE",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).then(() => {
                item.remove();
                renderAddButton();
            });
        }
    });

    item.appendChild(img);
    item.appendChild(button);
    return item;
}

function updateButtonText(button) {
    const total = carousel.querySelectorAll(".carousel-item").length;
    button.textContent = total <= 3 ? "↻" : "✖";
}

function createAddButton() {
    const addBtn = document.createElement("div");
    addBtn.className = "add-button";
    addBtn.textContent = "+";
    addBtn.addEventListener("click", () => {
        delete imageInput.dataset.replaceTarget;
        delete imageInput.dataset.replaceId;
        imageInput.click();
    });
    return addBtn;
}

function renderAddButton() {
    const existingAdd = carousel.querySelector(".add-button");
    if (existingAdd) existingAdd.remove();

    carousel.querySelectorAll(".remove-button").forEach(btn => updateButtonText(btn));

    const total = carousel.querySelectorAll(".carousel-item").length;
    if (total < MAX_IMAGES) {
        carousel.appendChild(createAddButton());
    }
}

imageInput.addEventListener("change", e => {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('image', file);

    const replaceId = imageInput.dataset.replaceId;

    if (replaceId) {
        formData.append('_method', 'PUT');

        fetch(`/vendor-previews/${replaceId}`, {
            method: "POST",
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                const imgs = carousel.querySelectorAll("img");
                imgs.forEach(img => {
                    if (img.src.includes(imageInput.dataset.replaceTarget)) {
                        img.src = '/' + data.preview.previewPicturePath;
                    }
                });
                delete imageInput.dataset.replaceTarget;
                delete imageInput.dataset.replaceId;
                imageInput.value = "";
            });
    } else {
        formData.append("vendorId", VENDOR_ID);

        fetch("/vendor-previews/upload", {
            method: "POST",
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                const item = createImageItem('/' + data.preview.previewPicturePath, data.preview.id);
                const addButton = carousel.querySelector(".add-button");
                if (addButton) carousel.insertBefore(item, addButton);
                else carousel.appendChild(item);
                renderAddButton();
                imageInput.value = "";
            });
    }
});


document.addEventListener("DOMContentLoaded", loadPreviews);

function resetDropzones() {
    // Remove existing previews & files
    if (imageDropzone) {
        imageDropzone.removeAllFiles(true);
    }
    if (menuDropzone) {
        menuDropzone.removeAllFiles(true);
    }
}


document.getElementById('packageModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('packageForm').reset();
    selectedCuisineIds.clear();
    document.getElementById('cuisineInputs').innerHTML = '';
    document.querySelectorAll('#cuisine-buttons button').forEach(btn => {
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-secondary');
    });

    resetDropzones();

    // Hapus preview image/pdf yang muncul secara manual (kalau masih bandel)
    document.querySelectorAll('.dz-preview').forEach(el => el.remove());
});

