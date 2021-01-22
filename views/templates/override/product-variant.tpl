{if !empty($group.attributes)}
    {assign 'groupAttributes' mdgcombinationduplicate::sortByKey($group.attributes)}

    <div class="clearfix product-variants-item">
        <span class="control-label">{$group.name}</span>
        {if $group.group_type == 'select'}
            <div class="h5 my-2 text-center">{l s='Unique product' mod='mdgcombinationduplicate'}</div>
            <select
                class="form-control form-control-select"
                id="group_{$id_attribute_group}"
                data-product-attribute="{$id_attribute_group}"
                name="group[{$id_attribute_group}]">
                {foreach $groupAttributes as $id_attribute => $group_attribute}
                    {if !in_array($id_attribute, $combinationsDuplicatedIdsAttributes)}
                        {include 'module:mdgcombinationduplicate/views/templates/override/product-variant-select.tpl'}
                    {/if}
                {/foreach}
            </select>
            <div class="h5 my-2 text-center">{l s='Or by offer of %d' sprintf=[$combinationDuplicatedSettings->quantity] mod='mdgcombinationduplicate'}</div>
            <select
                class="form-control form-control-select"
                id="group_{$id_attribute_group}"
                data-product-attribute="{$id_attribute_group}"
                name="group[{$id_attribute_group}]">
                {foreach $groupAttributes as $id_attribute => $group_attribute}
                    {if in_array($id_attribute, $combinationsDuplicatedIdsAttributes)}
                        {include 'module:mdgcombinationduplicate/views/templates/override/product-variant-select.tpl'}
                    {/if}
                {/foreach}
            </select>
        {elseif $group.group_type == 'color'}
            <div class="h5 my-2 text-center">{l s='Unique product' mod='mdgcombinationduplicate'}</div>
            <ul class="clearfix" id="group_{$id_attribute_group}">
                {foreach $groupAttributes as $id_attribute => $group_attribute}
                    {if !in_array($id_attribute, $combinationsDuplicatedIdsAttributes)}
                        {include 'module:mdgcombinationduplicate/views/templates/override/product-variant-color.tpl'}
                    {/if}
                {/foreach}
            </ul>
            <div class="h5 my-2 text-center">{l s='Or by offer of %d' sprintf=[$combinationDuplicatedSettings->quantity] mod='mdgcombinationduplicate'}</div>
            <ul class="clearfix" id="group_{$id_attribute_group}">
                {foreach $groupAttributes as $id_attribute => $group_attribute}
                    {if in_array($id_attribute, $combinationsDuplicatedIdsAttributes)}
                        {include 'module:mdgcombinationduplicate/views/templates/override/product-variant-color.tpl'}
                    {/if}
                {/foreach}
            </ul>
        {elseif $group.group_type == 'radio'}
            <div class="h5 my-2 text-center">{l s='Unique product' mod='mdgcombinationduplicate'}</div>
            <ul class="clearfix" id="group_{$id_attribute_group}">
                {foreach $groupAttributes as $id_attribute => $group_attribute}
                    {if !in_array($id_attribute, $combinationsDuplicatedIdsAttributes)}
                        {include 'module:mdgcombinationduplicate/views/templates/override/product-variant-radio.tpl'}
                    {/if}
                {/foreach}
            </ul>
            <div class="h5 my-2 text-center">{l s='Or by offer of %d' sprintf=[$combinationDuplicatedSettings->quantity] mod='mdgcombinationduplicate'}</div>
            <ul class="clearfix" id="group_{$id_attribute_group}">
                {foreach $groupAttributes as $id_attribute => $group_attribute}
                    {if in_array($id_attribute, $combinationsDuplicatedIdsAttributes)}
                        {include 'module:mdgcombinationduplicate/views/templates/override/product-variant-radio.tpl'}
                    {/if}
                {/foreach}
            </ul>
        {/if}
    </div>
{/if}
