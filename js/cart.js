/* =========================================
   CAFELIA CART JAVASCRIPT
========================================= */


// =========================================
// UPDATE QUANTITY
// =========================================

function updateQuantity(input, price, totalId) {

    if (!input) {
        return;
    }

    let quantity = parseInt(input.value);

    if (isNaN(quantity) || quantity < 1) {
        quantity = 1;
        input.value = 1;
    }

    const total = quantity * parseFloat(price);

    const totalElement = document.getElementById(totalId);

    if (totalElement) {
        totalElement.textContent =
            "₱" + total.toFixed(2);
    }

    updateCartTotal();

}


// =========================================
// INCREASE QUANTITY
// =========================================

function increaseCartQuantity(inputId, price, totalId) {

    const input = document.getElementById(inputId);

    if (!input) {
        return;
    }

    let quantity = parseInt(input.value) || 1;

    quantity++;

    input.value = quantity;

    updateQuantity(
        input,
        price,
        totalId
    );

}


// =========================================
// DECREASE QUANTITY
// =========================================

function decreaseCartQuantity(inputId, price, totalId) {

    const input = document.getElementById(inputId);

    if (!input) {
        return;
    }

    let quantity = parseInt(input.value) || 1;

    if (quantity > 1) {
        quantity--;
    }

    input.value = quantity;

    updateQuantity(
        input,
        price,
        totalId
    );

}


// =========================================
// UPDATE CART TOTAL
// =========================================

function updateCartTotal() {

    let total = 0;

    const cartItems = document.querySelectorAll(
        ".cart-item"
    );

    cartItems.forEach(function (item) {

        const priceElement =
            item.querySelector(".cart-price");

        const quantityElement =
            item.querySelector(".cart-quantity");

        if (!priceElement || !quantityElement) {
            return;
        }

        const price =
            parseFloat(
                priceElement.dataset.price
            ) || 0;

        const quantity =
            parseInt(
                quantityElement.value
            ) || 1;

        total += price * quantity;

    });


    const cartTotal =
        document.getElementById("cart-total");

    if (cartTotal) {

        cartTotal.textContent =
            "₱" + total.toFixed(2);

    }

}


// =========================================
// REMOVE CART ITEM CONFIRMATION
// =========================================

function removeCartItem() {

    return confirm(
        "Are you sure you want to remove this item from your cart?"
    );

}


// =========================================
// CHECKOUT CONFIRMATION
// =========================================

function confirmCheckout() {

    return confirm(
        "Are you sure you want to proceed with your order?"
    );

}


// =========================================
// CART READY
// =========================================

document.addEventListener(
    "DOMContentLoaded",
    function () {

        updateCartTotal();

    }
);