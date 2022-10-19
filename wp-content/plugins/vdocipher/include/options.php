<?php
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\r\n<html lang='en'><head>\r\n<title>404 Not Found</title>\r\n".
        "</head><body>\r\n<h1>Not Found</h1>\r\n<p>The requested URL " . $_SERVER['SCRIPT_NAME'] . " was not found on ".
        "this server.</p>\r\n</body></html>");
}
?>

<div class="wrap">
<h2>VdoCipher Options</h2>

<form name="vdoOptionForm" method="post" action="options.php">
<?php
settings_fields('vdo_option-group');
do_settings_sections('vdo_option-group');
?>
    <?php
    $existingKey = get_option('vdo_client_key');
    $keyIsValid = $existingKey && strlen($existingKey) == 64;
    $keyDescriptionDisplay = $keyIsValid ? 'none' : 'block';
    $restElementDisplay = $keyIsValid ? 'table-row-group' : 'none';
    ?>
    <table class="form-table">
        <tbody>
        <tr valign="top">
        <th scope="row"><label for="vdo_client_key">API Secret Key</label></th>
        <td>
            <div style="display: inline-flex;">
                <input id="vdo_client_key" name="vdo_client_key" style="width: 640px"
                       type="password" required minlength="64" maxlength="64"
                       value="<?php echo esc_attr(get_option('vdo_client_key')); ?>"/>
                <button id="toggle_API_visibility" data-protected="On" class="button" type="button">Show API Secret Key</button>
            </div>
            <p class="description" style="display: <?= $keyDescriptionDisplay ?>">
                API Key is a shared secret between your website servers and vdocipher dashboard. To generate this,
                login to vdocipher dashboard, go to "Config" > "API Keys". Generate a new key and copy-paste it here.
            </p>
        </td>
        </tr>
        </tbody>

        <tbody style="display: <?= $restElementDisplay ?>">
        <tr valign="top">
        <th scope="row"><label for="vdo_default_width">Default Width</label></th>
        <td>
            <input id="vdo_default_width" name="vdo_default_width"
                   type="number" required
                   value="<?php echo esc_attr(get_option('vdo_default_width')); ?>"
        /></td>
        </tr>

        <tr valign="top">
        <th scope="row"><label for="vdo_default_height">Default Height</label></th>
        <td>
            <input type="text" id="vdo_default_height" name="vdo_default_height"
                   required pattern="^auto|\d+$"
                   value="<?php echo esc_attr(get_option('vdo_default_height')); ?>"/>
            <p class="description">Can be either "auto" or a number. Set to "auto" height and max width for responsive layout.</p>
        </td>
        </tr>

        <tr>
        <th scope="row"><label for="vdo_player_speed">Playback speed</label></th>
        <td>
            <input type="text" id="vdo_player_speed" name="vdo_player_speed"
                   pattern="^\d.\d{1,2}(,\d.\d{1,2})+$"
              value="<?php echo esc_attr(get_option('vdo_player_speed')); ?>"
            />
            <p class="description">Speed can be defined as comma separated decimal values e.g. 0.75,1.0,1.25,1.5,1.75,2.0</p>
        </td>
        </tr>

        <!-- Version Number -->
        <?php
        $existingVersion = get_option('vdo_embed_version');
        $embedVersionDisplay = 'none';
        if ($existingVersion !== false && $existingVersion !== VDOCIPHER_PLAYER_VERSION) {
            $embedVersionDisplay = 'table-row';
        } ?>
        <tr valign="top" style="display: <?= $embedVersionDisplay ?>">
            <th scope="row"><label for="vdo_embed_version">Player Version</label></th>
            <td>
                <div style="display: inline-flex">
                    <input type="text" name="vdo_embed_version"
                           id="vdo_embed_version"
                           value="<?= $existingVersion ?>"
                           readonly />
                    <p class="vdo_saveChangeMessage" style="display: none; font-style: italic">Click on "save changes" below to confirm</p>
                    <button id="vdo_setDefaultVersionBtn" class="button" type="button">Use latest player version 1.6.10</button><br/>
                    <script>
                        document.querySelector('#vdo_setDefaultVersionBtn').addEventListener('click', (e) => {
                            e.preventDefault();
                            e.target.parentElement.querySelector('input').value = '<?= VDOCIPHER_PLAYER_VERSION ?>'
                            e.target.style.display = 'none';
                            e.target.parentElement.querySelector('.vdo_saveChangeMessage').style.display = '';
                        });
                    </script>
                </div>
                <p class="description">
                    It is recommended to use the latest player version for best video playback. This action
                    can not be reverted.
                </p>
            </td>
        </tr>

        <!-- Player Theme Options -->
        <?php
        $defaultThemeId = '9ae8bbe8dd964ddc9bdb932cca1cb59a';
        $existingTheme  = get_option('vdo_player_theme');
        $themeSettingDisplay = 'none';
        if ($existingTheme !== false && $existingTheme !== $defaultThemeId) {
            $themeSettingDisplay = 'table-row';
        } ?>
        <tr valign="top" style="display: <?= $themeSettingDisplay ?>">
        <th scope="row"><label for="vdo_player_theme">Player Theme</label></th>
        <td>
            <div style="display:inline-flex; margin-bottom:10px;">
                <input
                        type="text"
                        name="vdo_player_theme"
                        id="vdo_player_theme"
                        value="<?php echo $existingTheme; ?>"
                        maxlength="32"
                        style="width: 320px"
                        readonly
                />
                <p class="vdo_saveChangeMessage" style="display: none; font-style: italic">Click on "save changes" below to confirm</p>
                <button id="vdo_setDefaultThemeBtn" class="button" type="button">Revert to default theme</button><br/>
                <script>
                    document.querySelector('#vdo_setDefaultThemeBtn').addEventListener('click', (e) => {
                        e.preventDefault();
                        e.target.parentElement.querySelector('input').value = '<?= $defaultThemeId ?>'
                        e.target.style.display = 'none';
                        e.target.parentElement.querySelector('.vdo_saveChangeMessage').style.display = '';
                    });
                </script>
            </div>
            <p class="description">
                We have reverted player themes in preparation for something better. If you are having any styling
                issues in your player, please use the button above to revert to the default theme. Once you revert
                to the default theme, there is no way to undo the selection. The default theme is the one that is
                on your vdocipher dashboard when previewing any video.
            </p>
            <figure style="margin: 0">
                <img src="<?php echo plugin_dir_url(__FILE__).'img/default1.png' ?>"
                     style="width: 300px" alt="preview of default player">
                <figcaption>Screenshot of the default player theme</figcaption>
            </figure>
        </td>
        </tr>
        <!-- Player Theme Options end-->

        <!-- Player Watermark option - Flash/ HTML5 starts -->
        <?php
         $existingFlashSetting = get_option('vdo_watermark_flash_html');
         if ($existingFlashSetting === 'flash') { ?>
             <tr id="vdo_watermark_html_flash" valign="top">
                 <th scope="row"> Choice of Watermark </th>
                 <td>
                     <input type="radio" class="vdo-htmlflash"
                            value="html5" name="vdo_watermark_flash_html"
                            id="vdo_html5">
                     <label for="vdo_html5">HTML5 (Overlay)</label>
                     &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
                     <input type="radio" class="vdo-htmlflash"
                            value="flash" name="vdo_watermark_flash_html"
                            id="vdo_flash" checked >
                     <label for="vdo_flash">Flash (Hard-Coded)</label>
                     <p class="description">
                         IMPORTANT: Flash player is no longer supported by any modern browser. Selecting "Flash" here is
                         causing playback issues for your users. Please select HTML5 and "save changes" to use HTML5
                         playback. This change will be irreversible.
                     </p>
                 </td>
             </tr>

        <?php } ?>

        <!-- Player Watermark option - Flash/ HTML5 ends -->
        <tr valign="top">
        <th scope="row"><label for="vdo_watermarkjson">Annotation Statement</label></th>
        <td>
          <div style="display: inline-flex;">
              <textarea name="vdo_annotate_code" id="vdo_watermarkjson" rows="6" cols="55"
          ><?php
            if (get_option('vdo_annotate_code') != "") {
                echo get_option('vdo_annotate_code');
                $vdo_annotation_code = get_option('vdo_annotate_code');
            }
            ?></textarea>
          <p class="description" style="margin-left:20px; position: relative">
          <span style="color:purple"><b>Sample Code for Dynamic Watermark</b></span><br/>
          [{'type':'rtext', 'text':' {name}', 'alpha':'0.60', 'color':'0xFF0000','size':'15','interval':'5000'}] <br/>
          <span style="color:purple"><b>Sample Code for Static Watermark</b></span><br/>
          [{'type':'text', 'text':'{ip}', 'alpha':'0.5' , 'x':'10', 'y':'100', 'color':'0xFF0000', 'size':'12'}] <br/>
          </p>
          </div>
          <p class="description" id="vdojsonvalidator"></p>
          <p class="description">
                Leave this text blank in case you do not need watermark over all
                videos. For details on writing the annotation code
                <a href="https://www.vdocipher.com/blog/2014/12/add-text-to-videos-with-watermark/" target="_blank">
                    check this out
                </a>
          </p>
        </td>
        </tr>
        <tr style="display:none;">
          <td>Plugin version no.: </td>
          <td><input
            id="vdo_plugin_version" name="vdo_plugin_version" type="hidden"
            value="<?php echo esc_attr(get_option('vdo_plugin_version')); ?>" readonly>
          </td>
        </tr>

        </tbody>
    </table>
    <?php
        wp_enqueue_script('vdo_validate_watermark', plugin_dir_url(__FILE__).'js/validatewatermark.js');
        wp_enqueue_script('vdo_hide_key', plugin_dir_url(__FILE__).'js/showkey.js');
        ?>
<?php submit_button(); ?>
</form>
</div>
