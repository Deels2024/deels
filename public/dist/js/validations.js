const form = document.querySelector('.validated_form');

const requiredInputs = document.getElementsByClassName('required_input');

const uniqueEmails = document.getElementsByClassName('unique_email');

const uniqueUsernames = document.getElementsByClassName('unique_username');
const confirmedInputs = document.getElementsByClassName('confirmed_input');
const digitsInputs = document.getElementsByClassName('digits_input');

const checkNotEmptyInput = (el, min = 3, max = 200) => {

    let valid = false;

    const userVal = el.value.trim();

    if (!isRequired(userVal)) {
        showError(el, 'Поле не может быть пустым.');
    } else if (!isBetween(userVal.length, min, max)) {
        showError(el, el.dataset.minError || `Поле должно содержать от ${min} до ${max} символов.`)
    } else {
        showSuccess(el);
        valid = true;
    }

    return valid;
};

const checkDigits = (el) => {
    const phoneValue = el.value.trim();

    const isValid = /^\d+$/.test(phoneValue);
    if (isValid) {
        showSuccess(el);
    } else {
        console.log(el);
        showError(el, "Используйте только цифры");
    }

    return isValid;
};

const checkConfirmed = (el) => {
    const userVal = el.value.trim();
    const targetName = el.getAttribute("data-confirmed")
    const targetEl = targetName ? document.querySelector(`[name="${targetName}"]`) : null;
    const targetVal = targetEl ? targetEl.value.trim() : '';

    if (!userVal || !targetVal) {
        clearValidationError(el, 'Указанные пароли не совпадают');
        el.parentElement.classList.remove('success', 'valid');

        return false;
    }

    const minLength = Number(el.dataset.min || 0);
    const targetMinLength = Number(targetEl.dataset.min || 0);

    if (userVal.length < minLength || targetVal.length < targetMinLength) {
        clearValidationError(el, 'Указанные пароли не совпадают');
        el.parentElement.classList.remove('success', 'valid');

        return false;
    }

    if (userVal === targetVal) {
        showSuccess(el);

        return true;
    }

    showError(el, 'Указанные пароли не совпадают');

    return false;
};

async function checkInDatabase(email) {
    let res = await fetch(`/check-email-uniqueness?email=${encodeURIComponent(email)}`)

    return await res.json();
}

async function checkInDatabaseUsername(username) {
    let res = await fetch(`/check-username-uniqueness?username=${encodeURIComponent(username)}`)

    res = await res.json();

    return res.username_exists;
}

async function checkUniqueEmail(el) {
    const email = el.value.trim();

    let valid = validateEmail(email);

    if (!valid) {
        showError(el, 'Проверьте корректность введенных данных')
        return valid;
    }

    const availability = await checkInDatabase(email)
    valid = availability.email_exists;

    if (el.value.trim() !== email) {
        el.parentElement.classList.remove('valid');
        return false;
    }

    if (!valid) {
        showError(el, availability.error || 'Такая почта уже существует!')
        return valid
    }

    showSuccess(el);

    return valid;
}

async function checkUniqueUsername(el) {
    const username = el.value.trim();


    /*
Usernames can only have:
- Lowercase Letters (a-z)
- Numbers (0-9)
- Dots (.)
- Underscores (_)
*/

    let valid = validateUsername(username);

    if (!valid) {
        showError(el, 'Используйте только латинские буквы, цифры, нижнее подчеркивание или точки')
        return valid;
    }

    valid = await checkInDatabaseUsername(username)

    if (el.value.trim() !== username) {
        el.parentElement.classList.remove('valid');
        return false;
    }

    if (!valid) {
        showError(el, 'Такой ник уже используется!')
        return valid
    } else {
        showOk(el)
    }

    showSuccess(el);

    return valid;
}


const isRequired = value => value !== '';
const isBetween = (length, min, max) => !(length < min || length > max);
const validateEmail = (email) => {
    return String(email)
        .toLowerCase()
        .match(
            /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        );
};

const validateUsername = (username) => {
    /*
   Usernames can only have:
   - Lowercase Letters (a-z)
   - Numbers (0-9)
   - Dots (.)
   - Underscores (_)
 */
    return String(username)
        .toLowerCase()
        .match(
            /^[a-z0-9_\.]+$/
        );
};


const showError = (input, message) => {
    // get the form-field element
    const formField = input.parentElement;
    // add the error class
    formField.classList.remove('success');
    formField.classList.remove('valid');
    formField.classList.add('error');

    // show the error message
    const error = formField.querySelector('small');
    if (error) {
        error.textContent = message;
    }
};

const clearValidationError = (input, message) => {
    const formField = input.parentElement;
    const error = formField.querySelector('small');

    if (error && error.textContent === message) {
        error.textContent = '';
        formField.classList.remove('error');
        formField.classList.remove('valid');
    }
};

const showOk = (input) => {
    // get the form-field element
    const formField = input.parentElement;
    // add the error class
    formField.classList.remove('error');
    formField.classList.add('valid');
};

const showSuccess = (input) => {
    // get the form-field element
    const formField = input.parentElement;

    // remove the error class
    formField.classList.remove('error');
    formField.classList.add('success');

    // hide the error message
    const error = formField.querySelector('small');
    if (error) {
        error.textContent = '';
    }
}


form.addEventListener('submit', async function (e) {

    e.preventDefault();

    let allInputsValid = true;

    for (let i = 0; i < requiredInputs.length; ++i) {
        const input = requiredInputs[i];
        const min = Number(input.dataset.min || 3);
        const max = Number(input.dataset.max || 200);
        allInputsValid = checkNotEmptyInput(input, min, max) && allInputsValid;
    }

    for (let i = 0; i < uniqueEmails.length; ++i) {
        allInputsValid = await checkUniqueEmail(uniqueEmails[i]) && allInputsValid;
    }
    for (let i = 0; i < uniqueUsernames.length; ++i) {
        allInputsValid = await checkUniqueUsername(uniqueUsernames[i]) && allInputsValid;
    }

    for (let i = 0; i < confirmedInputs.length; ++i) {
        allInputsValid = await checkConfirmed(confirmedInputs[i]) && allInputsValid;
    }

    for (let i = 0; i < digitsInputs.length; ++i) {
        allInputsValid = checkDigits(digitsInputs[i]) && allInputsValid;
    }


    if (allInputsValid) {
        form.submit();
    }
});


const debounce = (fn, delay = 500) => {
    let timeoutId;
    return (...args) => {
        // cancel the previous timer
        if (timeoutId) {
            clearTimeout(timeoutId);
        }

        timeoutId = setTimeout(() => {
            fn.apply(null, args)
        }, delay);
    };
};

form.addEventListener('input', function (e) {
    const formField = e.target.closest('.form-field');
    if (formField) {
        formField.classList.remove('valid');
    }
});

form.addEventListener('input', debounce(function (e) {
    switch (e.target.className) {
        case 'required_input':
            checkNotEmptyInput(
                e.target,
                Number(e.target.dataset.min || 3),
                Number(e.target.dataset.max || 200)
            );
            break;
        case 'unique_email':
            checkUniqueEmail(e.target);
            break;
        case 'unique_username':
            checkUniqueUsername(e.target);
            break;
        case 'confirmed_input':
            checkConfirmed(e.target);
            break;
        case 'digits_input':
            checkDigits(e.target);
            break;


    }
}));


for (let i = 0; i < requiredInputs.length; ++i) {
    requiredInputs[i].addEventListener('keyup', (e) => {
        const min = Number(e.target.dataset.min || 3);
        const max = Number(e.target.dataset.max || 200);
        checkNotEmptyInput(e.target, min, max)
    });
}

for (let i = 0; i < confirmedInputs.length; ++i) {
    confirmedInputs[i].addEventListener('keyup', (e) => {
        checkConfirmed(e.target)
    });
}

for (let i = 0; i < digitsInputs.length; ++i) {
    digitsInputs[i].addEventListener('keyup', (e) => {
        checkDigits(e.target)
    });
}


for (let i = 0; i < uniqueEmails.length; ++i) {
    uniqueEmails[i].addEventListener('keyup', async (e) => {
        await checkUniqueEmail(e.target)
    });
}

for (let i = 0; i < uniqueUsernames.length; ++i) {
    uniqueUsernames[i].addEventListener('keyup', async (e) => {
        await checkUniqueUsername(e.target)
    });
}
