<div title="{__("cp_pr_review_video")}" id="cp_review_player_{$post_id}_{$video_id}">
    <div class="cp-pr__popup-video">
        <iframe width="100%" height="100%" 
            src="{$smarty.const.CP_PR_YOUTUBE_PLAYER_URL}embed/{$youtube_id}?enablejsapi=1" frameborder="0" data-yt-id="ytplayer_{$video_id}" id="{$youtube_id}" 
            allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
        </iframe>
    </div>
<!--cp_review_player_{$post_id}_{$video_id}--></div>