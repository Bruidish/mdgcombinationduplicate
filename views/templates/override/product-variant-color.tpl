<li class="float-xs-left input-container">
    <label aria-label="{$group_attribute.name}">
    <input class="input-color" type="radio" data-product-attribute="{$id_attribute_group}" name="group[{$id_attribute_group}]" value="{$id_attribute}" title="{$group_attribute.name}"{if $group_attribute.selected} checked="checked"{/if}>
    <span
        {if $group_attribute.texture}
        class="color texture" style="background-image: url({$group_attribute.texture})"
        {elseif $group_attribute.html_color_code}
        class="color" style="background-color: {$group_attribute.html_color_code}"
        {/if}
    ><span class="sr-only">{$group_attribute.name}</span></span>
    </label>
</li>
