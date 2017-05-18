<?php 

function post($key) {
    if (isset($_POST[$key]))
        return $_POST[$key];
}

if (count($_POST) == 0) {
    goto NO_POST;
}

if ($name = post("ad_name")) {
    $name = sanitize_text_field($name);
} else {
    $errors[] = "Navn er påkrevd.";
}

if ($email = post("ad_email")) {
    if (!is_email($email))
        $errors[] = 'E-postadressen er ikke gyldig.';
    $email = sanitize_text_field($email);
} else {
    $errors[] = "E-post er påkrevd.";
}

if ($phone = post("ad_phone")) {
    $phone = sanitize_text_field($phone);
} else {
    $errors[] = "Telefon-nummer er påkrevd.";
}

if ($expire = post("ad_expire")) {
    $expire = intval($expire);
    if ($expire<=0 || $expire>3) {
        $errors[] = "Du må velge en annonsetid.";
    } else {
        $expire_date = time() + (
            60  // Seconds
            *60 // Minutes 
            *24 // Hours
            *(
                7 // Days
                *$expire // Number of weeks
            )
        );
    }
} else {
    $errors[] = "Du må velge en annonsetid.";
}
 
if ($title = post("ad_title")) {
    $title = sanitize_text_field($title);
} else {
    $errors[] = "Overskrift er påkrevd.";
}

if ($text = post("ad_text")) {
    $text = sanitize_text_field($text);
    if (strlen($text) > 150) {
        $errors[] = "Lengden på din annonse-tekst er på ".strlen($text)." av 150 mulige bokstaver.";
    }
} else {
    $errors[] = "Annonse-tekst er påkrevd";
}

if (isset($errors) && count($errors)>0) {
    goto NO_POST;
}

$post_info = array(
    'email' => $email,
    'name' => $name,
    'phone' => $phone,
);

$annonse_id = wp_insert_post(array(
    'post_title' => $title,
    'post_content' => $text,
    'post_type' => 'ad_posts'
));

add_post_meta($annonse_id, 'ad_posts_info', json_encode($post_info));
add_post_meta($annonse_id, 'ad_posts_expire', $expire_date);
$success = true;
unset($_POST);

NO_POST:

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
