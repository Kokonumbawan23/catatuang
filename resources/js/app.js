

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function formatIDR(input) {
    let value = input.value.replace(/[^\d]/g, '');
    if (value === '') {
        input.value = 'Rp 0';
        return;
    }
    const num = parseInt(value, 10);
    input.value = 'Rp ' + num.toLocaleString('id-ID');
}

function parseIDR(input) {
    return input.value.replace(/[^\d]/g, '');
}

document.querySelectorAll('.idr-input').forEach(function(input) {
    if (input.value && /^\d+$/.test(input.value)) {
        const num = parseInt(input.value, 10);
        input.value = 'Rp ' + num.toLocaleString('id-ID');
    }

    input.addEventListener('input', function() {
        formatIDR(this);
    });
    input.addEventListener('blur', function() {
        if (this.value === 'Rp 0' || this.value === 'Rp ') {
            this.value = 'Rp 0';
        }
    });
    input.addEventListener('focus', function() {
        const raw = parseIDR(this);
        if (raw === '0') {
            this.value = 'Rp 0';
        } else {
            this.value = 'Rp ' + raw;
            this.setSelectionRange(3, this.value.length);
        }
    });

    const form = input.closest('form');
    if (form) {
        form.addEventListener('submit', function() {
            input.value = parseIDR(input);
        });
    }
});
