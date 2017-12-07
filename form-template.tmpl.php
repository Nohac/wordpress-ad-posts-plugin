<?php

function post($key) {
    if (isset($_POST[$key]))
        return $_POST[$key];
}

$form_data = array(
    'name' => '',
    'email' => '',
    'phone' => '',
    'expire' => '',
    'title' => '',
    'text' => '',
};

// Validates form input ande returns an array with sanitized input and list of
// error messages.
// @Param $form_input An associative array with fields to be validated (use $form_data)
// @Return array('errors', // string array, validation errors
//               'form_data', // string array, a sanitized version of the input ($form_input)
//               )
function validate_form_input($form_input) {
    $errors = array();

    // These if-checks triggers if the array fields have content (i.e. not
    // NULL or '')
    if ($form_input['name']) {
        $form_input['name'] = sanitize_text_field($form_input['name']);
    } else {
        $errors[] = "Navn er p&aring;krevd.";
    }

    if ($form_input['email']) {
        if (!is_email($form_input['email'])0
            $errors[] = 'E-postadressen er ikke gyldig.';
        $form_input['email'] = sanitize_text_field($form_input['email']);
    } else {
        $errors[] = "E-post er p&aring;krevd.";
    }

    if ($form_input['phone']) {
        $form_input['phone'] = sanitize_text_field($form_input['phone']);
    } else {
        $errors[] = "Telefonnummer er p&aring;krevd.";
    }


    if ($form_input['expire']) {
        $form_input['expire'] = intval($form_input['expire']);
        if ($form_input['expire']<=0 || $form_input['expire']>3) {
            $errors[] = "Du må velge en annonsetid.";
        } else {
            $expire_date = time() + (
                60  // Seconds
                *60 // Minutes
                *24 // Hours
                *(
                    7 // Days
                    *$form_input['expire'] // Number of weeks
                )
            );
        }
    } else {
        $errors[] = "Du må velge en annonsetid.";
    }


    if ($form_input['title']) {
        $form_input['title'] = sanitize_text_field($form_input['title']);
    } else {
        $errors[] = "Overskrift er p&aring;krevd.";
    }

    if ($form_input['text']) {
        $form_input['text'] = sanitize_text_field($form_input['text']);
        if (strlen($form_input['text']) > 150) {
            $errors[] = "Lengden på din melding er på ".strlen($form_input['text'])." av 150 mulige bokstaver.";
        }
    } else {
        $errors[] = "Annonse-tekst er p&aring;krevd";
    }

    return array('form_date' => $form_input, 'errors' => $errors);
}

if (isset($_GET['success']) && $_GET['success'] == 'true') {
    // A form has already been submitted. Set success=true so we can show
    // a confirmation box.
    $success = true;
} else if (count($_POST) > 0) {

    $form_data['name'] = post("ad_name");
    $form_data['email'] = post("ad_email");
    $form_data['phone'] = post("ad_phone");
    $form_data['expire'] = post("ad_expire");
    $form_data['title'] = post("ad_title");
    $form_data['text'] = post("ad_text");

    $ret = validate_form_input($form_data);
    $errors = $ret['errors'];
    $data = $ret['form_data'];


    if (!(isset($ret['errors']) && count($ret['errors'])>0)) {
        $post_info = array(
            'email' => $data['email'],
            'name' => $data['name'],
            'phone' => $data['phone'],
        );

        $annonse_id = wp_insert_post(array(
            'post_title' => $data['title'],
            'post_content' => $data['text'],
            'post_type' => 'ad_posts'
        ));

        add_post_meta($annonse_id, 'ad_posts_info', json_encode($post_info));
        add_post_meta($annonse_id, 'ad_posts_expire', $expire_date);
        unset($_POST);
    }
}
?>

<div class="ad-posts-submit">
    <?php if (isset($success)): ?>
    <div class="error" style="width: 100%; padding: 10px; background-color: #7cd170; color: white;">
    Din annonse har blit sendt inn til vurdering og vil snart dukke opp på vår nettside. <br>
    Ha en fin dag!
    </div>
    <?php endif ?>
    <form action="" method="post">
        <label>Navn</label>
        <input type="text" name="ad_name" value="<?= post('ad_name') ?>"/>
        <br>
        <label>E-post</label>
        <input type="text" name="ad_email" value="<?= post('ad_email') ?>"/>
        <br>
        <label>Telefon</label>
        <input type="text" name="ad_phone" value="<?= post('ad_phone') ?>"/>
        <br>
        <label>Annonsen utgår om</label>
        <select name="ad_expire" style="width: 100%;">
            <option value="1" >En uke</option>
            <option value="2">To uker</option>
            <option value="3">Tre uker</option>
        </select>
        <br>
        <br>
        <label>Overskrift</label>
        <input type="text" name="ad_title" value="<?= post('ad_title') ?>"/>
        <br>
        <label>Annonse-tekst</label>
        maks 150 bokstaver
        <textarea name="ad_text"><?= post('ad_text') ?></textarea>
        <br>
        <?php if (isset($errors)): ?>
        <div class="error" style="width: 100%; padding: 10px; background-color: #d87f7f; color: white;">
            <ul style="all: unset; !IMPORTANT">
            <?php foreach($errors as $error): ?>
                <li>
                    <?= $error."<br>" ?>
                </li>
            <?php endforeach ?>
            </ul>
        </div>
        <br>
        <?php endif ?>
        <button>Send inn annonse</button>
    </form>
</div>
