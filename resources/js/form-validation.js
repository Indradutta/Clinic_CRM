/**
 * MediFlow Enterprise Form Validation Suite
 * Provides comprehensive client-side validation, removes native browser popups (orange exclamation marks),
 * renders accessible inline errors, and synchronizes clinical fields (DOB <-> Age, appointment slots, phone numbers).
 */

function getFieldLabel(input) {
    if (input.dataset.label) return input.dataset.label;
    
    // Look for associated label by "for" attribute
    if (input.id) {
        const labelEl = document.querySelector(`label[for="${input.id}"]`);
        if (labelEl) {
            return cleanLabelText(labelEl.textContent);
        }
    }
    
    // Look for parent or preceding label
    const parentContainer = input.closest('div');
    if (parentContainer) {
        const labelEl = parentContainer.querySelector('label');
        if (labelEl) {
            return cleanLabelText(labelEl.textContent);
        }
    }

    if (input.placeholder && !input.placeholder.startsWith('e.g.')) {
        return input.placeholder;
    }

    // Fallback based on name
    const rawName = input.name || '';
    const cleanName = rawName.replace(/\[.*?\]/g, '').replace(/_/g, ' ').trim();
    if (cleanName) {
        return cleanName.charAt(0).toUpperCase() + cleanName.slice(1);
    }

    return 'This field';
}

function cleanLabelText(text) {
    return text.replace(/\*/g, '').replace(/⚠️/g, '').trim();
}

function getErrorContainer(input) {
    const parent = input.closest('div') || input.parentElement;
    return parent;
}

function clearFieldError(input) {
    input.classList.remove('!border-rose-500', '!focus:border-rose-500', '!focus:ring-rose-200', 'border-rose-500');
    input.removeAttribute('aria-invalid');

    const container = getErrorContainer(input);
    if (!container) return;

    const existingError = container.querySelector('.js-client-validation-error');
    if (existingError) {
        existingError.remove();
    }
}

function showFieldError(input, message) {
    clearFieldError(input);

    input.classList.add('!border-rose-500', '!focus:border-rose-500', '!focus:ring-rose-200');
    input.setAttribute('aria-invalid', 'true');

    const container = getErrorContainer(input);
    if (!container) return;

    const errDiv = document.createElement('div');
    errDiv.className = 'js-client-validation-error mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-rose-600 animate-fadeIn';
    errDiv.setAttribute('role', 'alert');
    errDiv.innerHTML = `
        <svg class="w-3.5 h-3.5 shrink-0 text-rose-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        <span class="break-words">${message}</span>
    `;

    // Append after input or input wrapper
    if (input.nextElementSibling && input.nextElementSibling.classList.contains('auth-password-toggle')) {
        input.parentElement.after(errDiv);
    } else {
        container.appendChild(errDiv);
    }
}

function isIndianPhoneNumberField(input) {
    const name = (input.name || '').toLowerCase();
    const type = (input.type || '').toLowerCase();
    const pattern = input.getAttribute('pattern') || '';

    return (
        name.includes('phone') ||
        name.includes('mobile') ||
        name.includes('contact_number') ||
        pattern.includes('6-9') ||
        type === 'tel'
    );
}

function validateField(input) {
    // Skip hidden inputs, buttons, disabled fields
    if (input.type === 'hidden' || input.type === 'submit' || input.type === 'button' || input.disabled) {
        return { valid: true };
    }

    const value = (input.value !== undefined ? input.value : '').trim();
    const label = getFieldLabel(input);
    const isRequired = input.hasAttribute('required') || input.required;
    const name = (input.name || '').toLowerCase();

    // 1. Required Check
    if (isRequired && (!value || value === '')) {
        if (input.tagName === 'SELECT') {
            return { valid: false, message: `Please select ${label.toLowerCase()}.` };
        }
        return { valid: false, message: `${label} is required.` };
    }

    // If empty and not required, pass
    if (!value) {
        return { valid: true };
    }

    // 2. Phone Number Validation
    if (isIndianPhoneNumberField(input)) {
        const digits = value.replace(/\D/g, '');
        if (digits.length !== 10) {
            return {
                valid: false,
                message: 'Phone number must be exactly 10 digits.'
            };
        }
        if (!/^[6-9]/.test(digits)) {
            return {
                valid: false,
                message: 'Mobile number must start with 6, 7, 8, or 9 (e.g. 98765 43210).'
            };
        }
    }

    // 3. Email Validation
    if (input.type === 'email' || name.includes('email')) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            return {
                valid: false,
                message: 'Please enter a valid email address (e.g. name@example.com).'
            };
        }
    }

    // 4. Appointment Date Validation
    if (name.includes('appointment_date')) {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const todayStr = `${year}-${month}-${day}`;

        if (value < todayStr) {
            return {
                valid: false,
                message: 'Appointment date cannot be in the past. Please select today or a future date.'
            };
        }
    }

    // 5. Appointment Time Slot Validation (today check)
    if (name.includes('appointment_time')) {
        const form = input.closest('form');
        const dateInput = form ? form.querySelector('input[name="appointment_date"]') : null;
        if (dateInput && dateInput.value) {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const todayStr = `${year}-${month}-${day}`;

            if (dateInput.value === todayStr) {
                const currentHours = now.getHours();
                const currentMinutes = now.getMinutes();
                const [slotHours, slotMinutes] = value.split(':').map(Number);

                if (!isNaN(slotHours) && !isNaN(slotMinutes)) {
                    if (slotHours < currentHours || (slotHours === currentHours && slotMinutes <= currentMinutes)) {
                        return {
                            valid: false,
                            message: 'This time slot has already passed for today. Please select an upcoming slot.'
                        };
                    }
                }
            }
        }
    }

    // 6. Date of Birth Validation
    if (name === 'dob' || name.includes('date_of_birth')) {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const todayStr = `${year}-${month}-${day}`;

        if (value > todayStr) {
            return {
                valid: false,
                message: 'Date of birth cannot be in the future.'
            };
        }
    }

    // 7. Age Validation
    if (name === 'age') {
        const ageNum = parseInt(value, 10);
        if (isNaN(ageNum) || ageNum < 0 || ageNum > 130) {
            return {
                valid: false,
                message: 'Please enter a valid age between 0 and 130.'
            };
        }

        // Check if matching DOB if DOB is filled
        const form = input.closest('form');
        const dobInput = form ? form.querySelector('input[name="dob"]') : null;
        if (dobInput && dobInput.value) {
            const birthDate = new Date(dobInput.value);
            const today = new Date();
            let calcAge = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                calcAge--;
            }
            if (calcAge >= 0 && Math.abs(calcAge - ageNum) > 1) {
                return {
                    valid: false,
                    message: `Age (${ageNum}) does not match entered date of birth (${calcAge} years).`
                };
            }
        }
    }

    // 8. Due Date vs Invoice Date
    if (name === 'due_date') {
        const form = input.closest('form');
        const invoiceDateInput = form ? form.querySelector('input[name="invoice_date"]') : null;
        if (invoiceDateInput && invoiceDateInput.value && value < invoiceDateInput.value) {
            return {
                valid: false,
                message: 'Payment due date cannot be earlier than invoice date.'
            };
        }
    }

    // 9. Numeric inputs (Quantity, Prices, Fees)
    if (input.type === 'number') {
        const numVal = parseFloat(value);
        if (isNaN(numVal)) {
            return { valid: false, message: `${label} must be a valid number.` };
        }

        const min = input.getAttribute('min');
        if (min !== null && numVal < parseFloat(min)) {
            return { valid: false, message: `${label} must be at least ${min}.` };
        }

        const max = input.getAttribute('max');
        if (max !== null && numVal > parseFloat(max)) {
            return { valid: false, message: `${label} cannot exceed ${max}.` };
        }
    }

    // 10. Vitals (BP format)
    if (name.includes('vital_bp') || name.includes('blood_pressure')) {
        if (!/^\d{2,3}\/\d{2,3}$/.test(value)) {
            return {
                valid: false,
                message: 'Enter blood pressure in systolic/diastolic format (e.g. 120/80).'
            };
        }
    }

    // 11. Pattern match if pattern attribute exists
    const pattern = input.getAttribute('pattern');
    if (pattern) {
        const regex = new RegExp(`^(?:${pattern})$`);
        if (!regex.test(value)) {
            return {
                valid: false,
                message: `Please enter a valid format for ${label.toLowerCase()}.`
            };
        }
    }

    // 12. Minlength check
    const minLength = input.getAttribute('minlength');
    if (minLength && value.length < parseInt(minLength, 10)) {
        return {
            valid: false,
            message: `${label} must be at least ${minLength} characters.`
        };
    }

    // 13. Password Confirmation
    if (name === 'password_confirmation') {
        const form = input.closest('form');
        const passwordInput = form ? form.querySelector('input[name="password"], input[name="new_password"]') : null;
        if (passwordInput && passwordInput.value !== value) {
            return {
                valid: false,
                message: 'Password confirmation does not match password.'
            };
        }
    }

    return { valid: true };
}

function showToastNotification(message) {
    const existingToast = document.getElementById('mediflow-validation-toast');
    if (existingToast) {
        existingToast.remove();
    }

    const toast = document.createElement('div');
    toast.id = 'mediflow-validation-toast';
    toast.className = 'fixed top-5 right-5 z-[9999] flex items-center gap-3 px-4 py-3 bg-rose-600 text-white text-xs font-semibold rounded-2xl shadow-xl border border-rose-500 animate-slideDown';
    toast.innerHTML = `
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span>${message}</span>
        <button type="button" class="ml-2 hover:opacity-75" onclick="this.parentElement.remove()">✕</button>
    `;

    document.body.appendChild(toast);
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 4500);
}

export function initFormValidation() {
    // 1. Process all forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        // Enforce novalidate so the native orange exclamation mark popup never appears
        form.setAttribute('novalidate', 'true');

        // Form submit listener
        form.addEventListener('submit', function (e) {
            const inputs = form.querySelectorAll('input, select, textarea');
            let hasError = false;
            let firstInvalid = null;

            inputs.forEach(input => {
                const result = validateField(input);
                if (!result.valid) {
                    hasError = true;
                    showFieldError(input, result.message);
                    if (!firstInvalid) {
                        firstInvalid = input;
                    }
                } else {
                    clearFieldError(input);
                }
            });

            if (hasError) {
                e.preventDefault();
                e.stopPropagation();

                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }

                showToastNotification('Please review and correct the highlighted fields.');
            }
        });

        // Dynamic input listeners for real-time clearance and validation
        form.addEventListener('input', function (e) {
            const target = e.target;
            if (!target || !target.name) return;

            // Restrict phone numbers to digits only and max 10 digits
            if (isIndianPhoneNumberField(target)) {
                const originalVal = target.value;
                const digitsOnly = originalVal.replace(/\D/g, '').slice(0, 10);
                if (originalVal !== digitsOnly) {
                    target.value = digitsOnly;
                }
            }

            // Sync DOB with Age dynamically
            if (target.name === 'dob' || target.name.includes('date_of_birth')) {
                const ageInput = form.querySelector('input[name="age"]');
                if (ageInput && target.value) {
                    const birthDate = new Date(target.value);
                    const today = new Date();
                    let calcAge = today.getFullYear() - birthDate.getFullYear();
                    const m = today.getMonth() - birthDate.getMonth();
                    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                        calcAge--;
                    }
                    if (calcAge >= 0 && calcAge <= 130) {
                        ageInput.value = calcAge;
                        clearFieldError(ageInput);
                    }
                }
            }

            // If field currently has an error, re-check on input
            if (target.getAttribute('aria-invalid') === 'true') {
                const result = validateField(target);
                if (result.valid) {
                    clearFieldError(target);
                } else {
                    showFieldError(target, result.message);
                }
            }
        });

        form.addEventListener('change', function (e) {
            const target = e.target;
            if (!target) return;

            // When appointment date changes, revalidate appointment time if selected
            if (target.name === 'appointment_date') {
                const timeInput = form.querySelector('select[name="appointment_time"], input[name="appointment_time"]');
                if (timeInput && timeInput.value) {
                    const timeResult = validateField(timeInput);
                    if (!timeResult.valid) {
                        showFieldError(timeInput, timeResult.message);
                    } else {
                        clearFieldError(timeInput);
                    }
                }
            }

            if (target.getAttribute('aria-invalid') === 'true' || target.hasAttribute('required')) {
                const result = validateField(target);
                if (result.valid) {
                    clearFieldError(target);
                } else {
                    showFieldError(target, result.message);
                }
            }
        });

        form.addEventListener('blur', function (e) {
            const target = e.target;
            if (!target || !target.name) return;

            // On blur, if field is dirty or required, perform validation
            if (target.value || target.hasAttribute('required')) {
                const result = validateField(target);
                if (!result.valid) {
                    showFieldError(target, result.message);
                } else {
                    clearFieldError(target);
                }
            }
        }, true);
    });
}

// Auto-run on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFormValidation);
} else {
    initFormValidation();
}
