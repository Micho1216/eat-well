const { orders, isNextWeek, trans } = window.orderData;

const mealTypes = [
    { key: 'Morning', label: trans.breakfast },
    { key: 'Afternoon', label: trans.lunch },
    { key: 'Evening', label: trans.dinner }
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

function updateMealStatus(select, orderId, slot) {
    const status = select.value;
    select.classList.remove('preparing', 'delivering', 'received');
    select.classList.add(statusClassMap[status]);

    // Tampilkan loading biar user tahu sedang diproses
    Swal.fire({
        title: trans.loading || trans.processing,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch(`/delivery-status/${orderId}/${slot}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify({ status })
    })
        .then(r => r.ok ? r.json() : Promise.reject(r))
        .then(res => {
            if (!res.success) throw 'fail';

            // ✅ Update data array frontend biar re-render sesuai status baru
            const order = orders.find(o => o.id === orderId);
            if (order) {
                const today = new Date().toISOString().split('T')[0];
                const ds = order.delivery_statuses.find(d =>
                    d.slot === slot.toLowerCase() &&
                    d.delivery_date.split('T')[0] === today
                );
                if (ds) {
                    ds.status = status;
                }
            }

            Swal.fire({
                icon: 'success',
                title: trans.status_updated || trans.status_updated_success,
                text: trans.status_change_success || trans.status_update_saved,
                confirmButtonText: 'OK',
                confirmButtonColor: '#28a745',
                position: 'center',
                showConfirmButton: false,
                timer: 1000
            });

            // ⏱️ Tambahan ini yang penting: render ulang setelah update!
            setTimeout(() => renderOrders(), 100);
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: trans.status_update_failed || 'Gagal memperbarui status',
                text: trans.try_again || 'Silakan coba lagi nanti.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545',
                position: 'center'
            });
        });
}



/* --------- POP-UP CANCEL --------- */
function attachCancelHandlers() {
    document.querySelectorAll('.btn-cancel').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            Swal.fire({
                title: trans.are_you_sure,
                text: trans.cancel_message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: trans.yes_cancel,
                cancelButtonText: trans.no,
                reverseButtons: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
            }).then(r => {
                if (r.isConfirmed) {
                    fetch(`/orders/${id}/cancel`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        }
                    })
                        .then(res => {
                            if (res.ok) {
                                Swal.fire({
                                    icon: 'success',
                                    title: trans.canceled || trans.rejected_success,
                                    text: trans.cancel_success || trans.order_rejected_success,
                                    confirmButtonText: 'OK',
                                    confirmButtonColor: '#28a745',
                                    position: 'center',
                                    showConfirmButton: true
                                }).then(() => location.reload());

                            } else {
                                Swal.fire('Gagal', 'Gagal membatalkan order.', 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Network error', 'error'));
                }
            });
        });
    });
}

/* --------- RENDER ORDER KARTU --------- */
function renderOrders() {
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
                console.warn(trans.invalid_delivery_date, ds.delivery_date);
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


                let options = '';
                let selectElement = '';

                if (status === 'Prepared') {
                    options = `
        <option value="Prepared" selected>${trans.prepared}</option>
        <option value="Delivered">${trans.delivered}</option>
    `;
                } else if (status === 'Delivered') {
                    options = `
        <option value="Delivered" selected>${trans.delivered}</option>
        <option value="Arrived">${trans.arrived}</option>
    `;
                }

                if (status !== 'Arrived') {
                    selectElement = `
        <select class="form-select form-select-sm meal-select ${statusClassMap[status]}"
                onchange="updateMealStatus(this, ${order.id}, '${meal.key}')">
            ${options}
        </select>
    `;
                } else {
                    selectElement = `
        <span class="meal-status-text ${statusClassMap[status]}">${trans.arrived}</span>
    `;
                }

                mealSections += `
    <div class="meal-box">
        <div class="meal-box-header">
            <span>${meal.label}</span>
            ${selectElement}
        </div>
        <div class="meal-entries">${entries}</div>
    </div>`;

            });
        } else {
            order.order_items.forEach(it => {
                if (selectedPackage !== 'all' && it.package.name !== selectedPackage) return;
                mealSections += `
                    <div class="meal-entry ms-1 mb-1">
                        • ${it.package.name} (${it.quantity}x) — ${it.package_time_slot}
                    </div>`;
            });
        }

        const cancelBtn = isNextWeek
            ? `<button class="btn btn-danger w-100 mt-3 btn-cancel" data-id="${order.id}">${trans.decline_order}</button>`
            : '';

        orderContainer.innerHTML += `
            <div class="col-12 col-md-6 col-lg-4 d-flex">
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

/* --------- EVENT LISTENER --------- */
searchInput.addEventListener('input', renderOrders);
packageFilter.addEventListener('change', renderOrders);
renderOrders();
