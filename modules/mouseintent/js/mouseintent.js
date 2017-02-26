$(document).ready(function(){
    jQuery('#get_the_offer').live('click', function(){

    });

    bioEp.init({
        width: 700,
        height: 448,
        delay: 3,
        html: '<div id="newsletter_block_left"><form action="" method="post">' +
        '<div class="form-group">' +
        '<input class="inputNew form-control grey newsletter-input" id="newsletter-input" type="text" name="email" size="18" placeholder="Adresa de e-mail">' +
        '<button name="submitNewsletter" class="btn btn-default button button-small"  type="submit">Trimite!</button></div><input type="hidden" name="action" value="0" /></form></div>',
        cookieExp: 30
    });
});