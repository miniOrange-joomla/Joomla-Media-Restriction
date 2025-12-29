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
    updateButtonStates();
});

function toggleCallTimeField() {
    var callDateField = document.getElementById('call_date_field');
    var callTimeField = document.getElementById('call_time_field');
    var callDateInput = document.getElementById('call_date');
    var callTimeInput = document.getElementById('call_time');
    var setupCallRadio = document.getElementById('support_call');
    
    // Update button visual states
    updateButtonStates();
    
    if (setupCallRadio.checked) {
        callDateField.style.display = 'block';
        callTimeField.style.display = 'block';
        callDateInput.setAttribute('required', 'required');
        callTimeInput.setAttribute('required', 'required');
    } else {
        callDateField.style.display = 'none';
        callTimeField.style.display = 'none';
        callDateInput.removeAttribute('required');
        callTimeInput.removeAttribute('required');
        callDateInput.value = '';
        callTimeInput.value = '';
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