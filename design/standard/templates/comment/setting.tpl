{ezcss_require( 'comment.css' )}
<div id="ezcom_setting" class="ezcom-setting">
    {def $number_perpage = ezini( 'NotificationSettings', 'NumberPerPage', 'ezcomments.ini' )}
    {if $number_perpage|eq( '-1' )|not}
        <div class="ezcom-page">
            {def $total_page = $total_count|div( $number_perpage )|ceil}
            {'%current_page/%total_page'|i18n( 'extension/ezcomments/view/page', '', hash( '%current_page', $current_page, '%total_page', $total_page ) )}
            {if $current_page|gt( 1 )}
                 <span>
                  <a title="{"Previous page"|i18n( 'extension/ezcomments/view/page' )}" href={concat( 'comment/setting/' ,$current_page|sub( 1 ) )|ezurl}><</a>
                 </span>
            {/if}
            {if $current_page|lt($total_page)}
                 <span>
                  <a title="{"Next page"|i18n('extension/ezcomments/view/page')}" href={concat( 'comment/setting/', $current_page|sum( 1 ) )|ezurl}>></a> 
                 </span>
            {/if}
            {undef $total_page}
        </div>
    {/if}
    {undef $number_perpage}
    <form method="post">
        <input type="hidden" name="SubscriberID" value="{$subscriber.id}" />
        <div class="ezcom-setting-head">
            <div class="ezcom-setting-select">
                <span>
                    <label><input type="checkbox" /></label>
                </span>
            </div>
            <div class="ezcom-setting-content">
                <span>
                    {'Content'|i18n( 'extension/comment/setting' )}
                </span>
            </div>
            <div class="ezcom-setting-count">
                <span>
                    {'Comments'|i18n( 'extension/comment/setting' )}
                </span>
            </div>
        </div>
        {foreach $contentobject_list as $contentobject_array}
        {def $contentobject=$contentobject_array.contentobject}
            <div class="ezcom-setting-row">
                <div class="ezcom-setting-select">
                    <label>
                        <input type="checkbox" name="Checkbox{$contentobject.id}_{$contentobject_array.language_id}"
                           {if $contentobject_array.subscribed}checked="true"{/if} />
                        {'Subscribed'|i18n( 'extension/comment/setting' )}
                        <input type="hidden" name="CheckboxName[]" value="Checkbox{$contentobject.id}_{$contentobject_array.language_id}" />
                    </label>
                </div>
                <div class="ezcom-setting-count">
                    <span>
                        {$contentobject_array.comment_count}
                    </span>
                </div>
                <div class="ezcom-setting-content">
                    <span>
                        <a href={$contentobject.main_node.url_alias|ezurl}>
                            {$contentobject.name} 
                        </a>
                    </span>
                </div>
            </div>
         {undef $contentobject}
        {/foreach}
        <div class="ezcom-tool">
            <p>
                <input type="submit" value="Save" name="SaveButton" class="button" />
                <input type="reset" value="Reset" class="button" />
            </p>
        </div>
   </form>
</div>