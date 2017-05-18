<style>
    .ad-posts-container {
        width: 100%;
    }

    .ad-post-box {
        vertical-align: text-top;
        width: 45%;
        height: 200px;
        margin: 5px;
        display: inline-block;
    }

    .ad-post-title {
        margin-bottom: 5px;
        font-weight: bold;
    }

    .ad-post-text {
        margin-bottom: 5px;
    }
</style>

<div class="ad-posts-container">
    <?php foreach($data as $d): extract($d); ?>
    <div class="ad-post-box">
        <div class="ad-post-title">
            <?= $title ?>
        </div>
        <div class="ad-post-text">
            <?= $text ?>
        </div>
        <div class="ad-post-info">
            <i class="fa fa-envelope" aria-hidden="true"></i> <?= $email ?> 
            <br>
            <i class="fa fa-phone" aria-hidden="true"></i> <?= $phone ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
