function add_css_tab(element) {
    jQuery(".mo_nav_tab_active").removeClass("mo_nav_tab_active").removeClass("active");
    jQuery(element).addClass("mo_nav_tab_active");
}

function moCancelForm() {
    jQuery('#cancel_form').submit();
}

function mo_login_page() {
    jQuery('#customer_login_form').submit();
}

function moMediaBack() {
    jQuery('#mo_media_cancel_form').submit();
}

function create_file()
{
    jQuery('#create_file').submit();
}

function close_popup()
{
    jQuery('#close_popup').submit();
}

function showmodal()
{
    jQuery('#myModal').show();
}

function hidemodal()
{
    jQuery('#myModal').hide();
}
function moMediaUpgrade() {
    jQuery('a[href="#upgrade_plans"]').click();
    add_css_tab("#upgrade_tab");
}

jQuery(document).ready(function() {

    var input = document.querySelector('input[name=mo_media_restriction_file_types]');

    // initialize Tagify on the above input node reference
	tagify1 = new Tagify(input, {
        maxTags: 5,
        enforceWhitelist: true,
        whitelist: ["pdf", "png", "jpg", "doc", "gif"],
        blacklist: [] // In string format "hello","temp"
     
    });

    tagify1.on('invalid', onInvalidTag)
    function onInvalidTag(e){
        if(e.detail.message=='not allowed'){
            alert('Only png, jpg, gif, pdf, doc tags allowed in free version');
        }
        if(e.detail.message=='number of tags exceeded')
        {
            alert('Number of tags exceeded. You can\'t add any tags.');
        }
        
    }

    jQuery('.tagify').css("width", "100%");

    jQuery('form[name="f"]').on('submit', function(e) {
        var tags = tagify1 ? tagify1.value : [];
        if (!tags || tags.length === 0) {
            e.preventDefault();
            jQuery('#mo_file_types_error').show();
            jQuery('input[name="mo_media_restriction_file_types"]').closest('.mo_boot_col-sm-7').find('.tagify').css('border-color', 'red');
            return false;
        }
        jQuery('#mo_file_types_error').hide();
        jQuery('input[name="mo_media_restriction_file_types"]').closest('.mo_boot_col-sm-7').find('.tagify').css('border-color', '');
    });

    jQuery('.show_rules').click(function(){
        jQuery('.rules').show();
        jQuery('.file_restriction_UI').hide();
    });

    jQuery('.hide_rules').click(function(){
        jQuery('.rules').hide();
        jQuery('.file_restriction_UI').show();
    });
   
    jQuery('#auto_redirect_option').change(function(){
        if(jQuery(this).val()=='custom_url'|| jQuery(this).val()=='sso_url')
        {
            jQuery('#url_redirection').show();
        }else{
            jQuery('#url_redirection').hide();
        }
    });

    jQuery('#mo_enable_media_restriction').click(function(){
    
        if(jQuery('#mo_enable_media_restriction').is(':checked')){
          jQuery('#media_restriction_options').show();
        }
        else{
            jQuery('#media_restriction_options').hide();
        }
    });

});

function displayFileName() {
    var fileInput = document.getElementById('fileInput');
    var file = fileInput.files[0];

    if (file && file.name.endsWith('.json')) {
        document.getElementById('fileName').textContent = file.name; 
    } else {
        document.getElementById('fileName').textContent = "Please select a .json file.";
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Support form: capture browser timezone into hidden fields if present
    (function setClientTimezoneFields() {
        const tzEl = document.getElementById('moClientTimezone');
        const offsetEl = document.getElementById('moClientTimezoneOffset');
        const tzDisplay = document.getElementById('moClientTimezoneDisplay'); // optional

        if (!tzEl && !offsetEl && !tzDisplay) {
            return;
        }

        let tzName = '';
        try {
            tzName = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        } catch (e) {
            tzName = '';
        }

        // minutes behind UTC (e.g. Chicago winter = 360)
        const offsetMinutes = new Date().getTimezoneOffset();
        const sign = offsetMinutes > 0 ? '-' : '+';
        const abs = Math.abs(offsetMinutes);
        const hh = String(Math.floor(abs / 60)).padStart(2, '0');
        const mm = String(abs % 60).padStart(2, '0');
        const utcOffset = `${sign}${hh}:${mm}`;

        if (tzEl) tzEl.value = tzName;
        if (offsetEl) offsetEl.value = String(offsetMinutes);
        if (tzDisplay) {
            tzDisplay.value = tzName ? `${tzName} (UTC ${utcOffset})` : `UTC ${utcOffset}`;
        }
    })();

    updateButtonStates();
});

function toggleCallTimeField() {
    var callDateField     = document.getElementById('call_date_field');
    var callTimeField     = document.getElementById('call_time_field');
    var callTimezoneField = document.getElementById('call_timezone_field');
    var callDateInput     = document.getElementById('call_date');
    var callTimeInput     = document.getElementById('call_time');
    var callTimezoneInput = document.getElementById('call_timezone');
    var setupCallRadio    = document.getElementById('support_call');

    updateButtonStates();

    if (setupCallRadio.checked) {
        callDateField.style.display     = 'block';
        callTimeField.style.display     = 'block';
        callTimezoneField.style.display = 'block';
        callDateInput.setAttribute('required', 'required');
        callTimeInput.setAttribute('required', 'required');
        callTimezoneInput.setAttribute('required', 'required');

        // Auto-fill browser timezone if empty
        if (!callTimezoneInput.value) {
            try {
                var browserTz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                if (browserTz) callTimezoneInput.value = browserTz;
            } catch (e) {}
        }
    } else {
        callDateField.style.display     = 'none';
        callTimeField.style.display     = 'none';
        callTimezoneField.style.display = 'none';
        callDateInput.removeAttribute('required');
        callTimeInput.removeAttribute('required');
        callTimezoneInput.removeAttribute('required');
        callDateInput.value     = '';
        callTimeInput.value     = '';
        callTimezoneInput.value = '';
    }
}

function updateButtonStates() {
    var generalBtn = document.getElementById('general_query_btn');
    var callBtn = document.getElementById('setup_call_btn');
    var generalRadio = document.getElementById('support_general');
    var callRadio = document.getElementById('support_call');
    
    if (generalRadio.checked) {
        generalBtn.classList.add('active');
        callBtn.classList.remove('active');
    } else if (callRadio.checked) {
        callBtn.classList.add('active');
        generalBtn.classList.remove('active');
    }
}


document.addEventListener('DOMContentLoaded', function () {

    const list = document.getElementById('countryList');
    const select = document.getElementById('countrySelect');
    const hiddenInput = document.getElementById('countryCode');

    // If the current page doesn't have the phone dropdown, do nothing.
    if (!list || !select || !hiddenInput) {
        return;
    }

    // countries is defined in assets/js/countries.js
    if (typeof countries === 'undefined' || !Array.isArray(countries)) {
        // Avoid breaking the page if countries.js wasn't loaded for some reason.
        return;
    }

    function getFlagEmoji(countryCode) {
        if (!countryCode || typeof countryCode !== 'string' || countryCode.length !== 2) {
            return '';
        }
        const code = countryCode.toUpperCase();
        const A = 65;
        const REGIONAL_INDICATOR_A = 0x1F1E6; // 🇦
        const first = code.charCodeAt(0) - A + REGIONAL_INDICATOR_A;
        const second = code.charCodeAt(1) - A + REGIONAL_INDICATOR_A;
        try {
            return String.fromCodePoint(first, second);
        } catch (e) {
            return '';
        }
    }

    function setSelectedCountry(country) {
        const flagEl = select.querySelector('.flag');
        const dialEl = select.querySelector('.dial-code');
        if (!flagEl || !dialEl) {
            return;
        }

        // Selected view: ONLY flag + dial code (no country name)
        flagEl.className = 'flag';
        flagEl.textContent = getFlagEmoji(country.code);
        dialEl.textContent = `+${country.dial}`;
        hiddenInput.value = String(country.dial);
    }

    function normalizeForSearch(value) {
        return String(value || '').trim().toLowerCase();
    }

    // Search box (sticky at top of dropdown)
    const searchLi = document.createElement('li');
    searchLi.className = 'mo-country-search';
    searchLi.innerHTML = `
        <input
            type="text"
            id="moCountrySearch"
            class="mo-country-search-input"
            placeholder="Search country or code…"
            autocomplete="off"
            spellcheck="false"
        />
    `;
    list.appendChild(searchLi);

    const searchInput = searchLi.querySelector('input');

    // Build dropdown: show country name + dial code
    countries.forEach(country => {
        const li = document.createElement('li');
        li.dataset.name = normalizeForSearch(country.name);
        li.dataset.code = normalizeForSearch(country.code);
        li.dataset.dial = normalizeForSearch(country.dial);

        li.innerHTML = `
            <span class="flag" aria-hidden="true">${getFlagEmoji(country.code)}</span>
            <span class="name">${country.name}</span>
            <span class="dial">+${country.dial}</span>
        `;

        li.onclick = function () {
            setSelectedCountry(country);
            list.classList.remove('open');
        };

        list.appendChild(li);
    });

    // Initialize selected from hidden value (dial code) if present, else first country.
    const currentDial = String(hiddenInput.value || '').replace(/\D/g, '');
    const initial = countries.find(c => String(c.dial) === currentDial) || countries[0];
    if (initial) {
        setSelectedCountry(initial);
    }

    function applyFilter() {
        if (!searchInput) {
            return;
        }
        const q = normalizeForSearch(searchInput.value);
        const items = list.querySelectorAll('li');
        items.forEach(function (li) {
            if (li === searchLi) {
                return;
            }
            // divider/empty li safety
            if (!li.dataset) {
                return;
            }
            if (q === '') {
                li.style.display = '';
                return;
            }
            const haystack = `${li.dataset.name || ''} ${li.dataset.code || ''} ${li.dataset.dial || ''}`;
            li.style.display = haystack.includes(q) ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilter);
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                searchInput.value = '';
                applyFilter();
                list.classList.remove('open');
                select.focus && select.focus();
            }
        });
    }

    select.onclick = () => {
        const isOpening = !list.classList.contains('open');
        list.classList.toggle('open');
        if (isOpening && searchInput) {
            // reset filter on open and focus search
            searchInput.value = '';
            applyFilter();
            setTimeout(() => searchInput.focus(), 0);
        }
    };

    // Close on outside click
    document.addEventListener('click', function (e) {
        if (!select.contains(e.target) && !list.contains(e.target)) {
            list.classList.remove('open');
        }
    });
});
