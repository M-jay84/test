var checked = false;

function checkAll(form) {
    if (checked == false)
        checked = true;
    else
        checked = false;

    var length = document.getElementById(form).elements.length;

    for (i = 0; i < length; i++) {
        document.getElementById(form).elements[i].checked = checked;
    }
}

function SmileIT(smile, form, text) {
    document.forms[form].elements[text].value = document.forms[form].elements[text].value + " " + smile + " ";
    document.forms[form].elements[text].focus();
}