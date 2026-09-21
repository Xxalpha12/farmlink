// Farm Link — client-side helpers

document.addEventListener('DOMContentLoaded', function () {

  // Confirm before destructive actions (delete listing / order cancel)
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm(el.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    });
  });

  // Live client-side search filter on the browse-produce page
  var searchInput = document.getElementById('liveSearch');
  var categorySelect = document.getElementById('categoryFilter');
  var cards = document.querySelectorAll('[data-product-card]');

  function applyFilter() {
    var term = (searchInput ? searchInput.value : '').toLowerCase().trim();
    var cat = categorySelect ? categorySelect.value : '';
    var visibleCount = 0;

    cards.forEach(function (card) {
      var name = (card.getAttribute('data-name') || '').toLowerCase();
      var cardCat = card.getAttribute('data-category') || '';
      var matchesTerm = name.indexOf(term) !== -1;
      var matchesCat = !cat || cat === cardCat;
      var show = matchesTerm && matchesCat;
      card.style.display = show ? '' : 'none';
      if (show) visibleCount++;
    });

    var emptyMsg = document.getElementById('noResults');
    if (emptyMsg) {
      emptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  }

  if (searchInput) searchInput.addEventListener('input', applyFilter);
  if (categorySelect) categorySelect.addEventListener('change', applyFilter);

  // Order quantity -> live total price calculation
  var qtyInput = document.getElementById('orderQuantity');
  var totalOut = document.getElementById('orderTotal');
  if (qtyInput && totalOut) {
    var unitPrice = parseFloat(qtyInput.getAttribute('data-unit-price')) || 0;
    var maxQty = parseInt(qtyInput.getAttribute('max'), 10) || 999999;
    function updateTotal() {
      var qty = parseInt(qtyInput.value, 10) || 0;
      if (qty > maxQty) qty = maxQty;
      var total = qty * unitPrice;
      totalOut.textContent = '₦' + total.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    qtyInput.addEventListener('input', updateTotal);
    updateTotal();
  }

  // Password confirmation check on registration form
  var pw = document.getElementById('password');
  var pw2 = document.getElementById('confirm_password');
  var regForm = document.getElementById('registerForm');
  if (regForm && pw && pw2) {
    regForm.addEventListener('submit', function (e) {
      if (pw.value !== pw2.value) {
        e.preventDefault();
        alert('Passwords do not match.');
        pw2.focus();
      }
    });
  }
});
