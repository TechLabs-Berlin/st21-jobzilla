<?php

/**
 *  HTML code for Rendering Button
 *
 * @param $name - button name
 * @param $id  - button id name
 * @param $class - button class name
 * @param $type - button type , default- button
 * @param $content - button content
 * @param $icon_class - button icon class
 * @param $extra - extra attributes for button
 * return string
 *
 */
function render_button($name = '', $id = '', $class = 'btn green', $type = 'button', $content, $icon_class, $extra = '')
{
    if (strlen($icon_class) > 0) {
        
        $icon = "<i class='fa fa-$icon_class'> </i>";
    } else {
        $icon = '';
    }
    $button = "<button name='$name' class='$class' id='$id' type='$type' $extra> <span>$content</span> $icon </button>";
    
    return $button;
}


