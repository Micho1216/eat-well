const { orders, isNextWeek, trans } = window.orderData;

const mealTypes = [
    {
        key: 'Morning',
        label: trans.breakfast
    },
    {
        key: 'Afternoon',
        label: trans.lunch
    },
    {
        key: 'Evening',
        label: trans.dinner
    }
];
const statusClassMap = {
    Prepared: 'preparing',
    Delivered: 'delivering',
    Arrived: 'received'
};

const orderContainer = document.getElementById('order-container');
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const searchInput = document.getElementById('search-input');
const emptyMsg = document.getElementById('empty-msg');
const packageFilter = document.getElementById('package-filter');

/* --------- SIMPAN PERUBAHAN STATUS (untuk This Week) --------- */
function updateMealStatus(select, orderId, slot) {
    const status = select.value;
    select.classList.remove('preparing', 'delivering', 'received');
    select.classList.add(statusClassMap[status]);
    let url = `/delivery-status/${orderId}/${slot}`;

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify({
            status
        })
    })
        .then(r => r.ok ? r.json() : Promise.reject(r))
        .then(res => {
            if (!res.success) throw 'fail';
            location.reload();
        })
        .catch(() => alert('Save failed'));
}

/* --------- POP‑UP CANCEL --------- */
function attachCancelHandlers() { // <<< NEW
    document.querySelectorAll('.btn-cancel').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            Swal.fire({
                title: window.orderData.trans.are_you_sure,
                text: window.orderData.trans.cancel_message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: window.orderData.trans.yes_cancel,
                cancelButtonText: window.orderData.trans.no,
                reverseButtons: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
            }).then(r => {
                if (r.isConfirmed) {
                    let url2 = `/orders/${id}/cancel`;
                    fetch(url2, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        }
                    })
                        .then(res => {
                            if (res.ok) location.reload();
                        })
                        .catch(() => Swal.fire('Error', 'Network error', 'error'));
                }
            });
        });
    });
}

/* --------- RENDER KARTU --------- */
function renderOrders() {
    console.log("panggil dalem");
    const term = searchInput.value.trim().toLowerCase();
    const selectedPackage = packageFilter.value;

    orderContainer.innerHTML = '';
    let shown = 0;

    orders.forEach(order => {
        const orderIdStr = 'inv' + String(order.id).padStart(3, '0');
        if (term && !orderIdStr.includes(term)) return;

        if (selectedPackage !== 'all') {
            const ok = order.order_items.some(i => i.package.name === selectedPackage);
            if (!ok) return;
        }

        shown++;
        const deliveryMap = {};
        const today = new Date().toISOString().split('T')[0];

        order.delivery_statuses.forEach(ds => {
            try {
                const dsDate = ds.delivery_date.split('T')[0];
                if (dsDate === today) {
                    deliveryMap[ds.slot] = ds.status;
                }
            } catch (e) {
                console.warn(window.orderData.trans.invalid_delivery_date, ds.delivery_date);
            }
        });

        let mealSections = '';
        if (!isNextWeek) {
            mealTypes.forEach(meal => {
                const items = order.order_items.filter(i => i.package_time_slot === meal.key);
                const filtered = selectedPackage === 'all' ? items : items.filter(i => i.package.name === selectedPackage);
                if (!filtered.length) return;

                const entries = filtered.map(i =>
                    `<div class="meal-entry">${i.package.name} (${i.quantity}x)</div>`
                ).join('');

                const status = deliveryMap[meal.key.toLowerCase()] || 'Prepared';

                mealSections +=
                    `<div class="meal-box">
                        <div class="meal-box-header">
                            <span>${meal.label}</span>
                            <select class="form-select form-select-sm meal-select ${statusClassMap[status]}"
                                    onchange="updateMealStatus(this, ${order.id}, '${meal.key}')">
                                <option value="Prepared" ${status === 'Prepared' ? 'selected' : ''}>${window.orderData.trans.prepared}</option>
                                <option value="Delivered" ${status === 'Delivered' ? 'selected' : ''}>${window.orderData.trans.delivered}</option>
                                <option value="Arrived" ${status === 'Arrived' ? 'selected' : ''}>${window.orderData.trans.arrived}</option>
                            </select>
                        </div>
                        <div class="meal-entries">${entries}</div>
                    </div>`;
            });
        } else {
            order.order_items.forEach(it => {
                if (selectedPackage !== 'all' && it.package.name !== selectedPackage) return;
                mealSections +=
                    `<div class="meal-entry ms-1 mb-1">
                        • ${it.package.name} (${it.quantity}x) — ${it.package_time_slot}
                    </div>`;
            });
        }

        let cancelBtn = '';
        if (isNextWeek) {
            cancelBtn =
                `<button class="btn btn-danger w-100 mt-3 btn-cancel"
                    data-id="${order.id}">
                    ${window.orderData.trans.decline_order}
                </button>`;
        }

        orderContainer.innerHTML +=
            `<div class="col-12 col-md-6 col-lg-4 d-flex">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="order-header">Order #INV${String(order.id).padStart(3, '0')}</div>
                        <p class="mb-1">${order.user?.name || '-'}</p>
                        <p class="mb-1">${order.user?.phone || '-'}</p>
                        <p class="mb-1">${order.user?.address || '-'}</p>
                        <p class="mb-2 text-muted"><i>${order.user?.notes || '-'}</i></p>
                        ${mealSections}
                        ${cancelBtn}
                    </div>
                </div>
            </div>`;
    });

    emptyMsg.style.display = shown === 0 ? 'block' : 'none';
    attachCancelHandlers();
}

searchInput.addEventListener('input', renderOrders);
packageFilter.addEventListener('change', renderOrders);
console.log("masuk");
renderOrders();
