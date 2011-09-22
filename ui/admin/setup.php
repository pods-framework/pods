<div class="wrap pods_admin">

    <div id="icon-edit-pages" class="icon32"><br /></div>

    <h2>Pods Setup <a href="#" class="add-new-h2">Add New</a></h2>

    <!-- bulk actions -->
    <div class="tablenav"> 
        <div class="alignleft actions"> 
            <select name="action"> 
                <option value="-1" selected="selected">Bulk Actions</option> 
                <option value="trash">Delete</option> 
            </select> 
            <input type="submit" name="doaction" id="doaction" class="button-secondary action" value="Apply"  /> 
        </div>
        <div class='tablenav-pages'>
            <span class="displaying-num">## items</span>
            <a class='first-page' title='Go to the first page' href='http://pods/wp-admin/edit.php?post_type=page'>&laquo;&laquo;</a> 
            <a class='prev-page' title='Go to the previous page' href='http://pods/wp-admin/edit.php?post_type=page&#038;paged=1'>&laquo;</a> 
            <input class='current-page' title='Current page' type='text' name='paged' value='1' size='1' /> of 
            <span class='total-pages'>#</span> 
            <a class='next-page' title='Go to the next page' href='http://pods/wp-admin/edit.php?post_type=page&#038;paged=2'>&raquo;</a> 
            <a class='last-page' title='Go to the last page' href='http://pods/wp-admin/edit.php?post_type=page&#038;paged=2'>&raquo;&raquo;</a>
        </div> 
        <br class="clear" /> 
    </div>
    <!-- /bulk actions -->

    <!-- pods table -->
    <table class="widefat fixed pages" cellspacing="0"> 
        <thead> 
            <tr> 
                <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                    <input type="checkbox" />
                </th>
                <th scope="col" id="label" class="manage-column column-label sortable" style="">
                    <a href="#">
                        <span>Label</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
                <th scope="col" id="machine-name" class="manage-column column-machine-name sortable" style="">
                    <a href="#">
                        <span>Machine Name</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
                <th scope="col" id="entries" class="manage-column column-entries sortable" style="">
                    <a href="#">
                        <span>Entries</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
                <th scope="col" id="date" class="manage-column column-date sortable" style="">
                    <a href="#">
                        <span>Modified</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
            </tr> 
        </thead> 

        <tfoot> 
            <tr> 
                <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                    <input type="checkbox" />
                </th>
                <th scope="col" id="label" class="manage-column column-label sortable" style="">
                    <a href="#">
                        <span>Label</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
                <th scope="col" id="machine-name" class="manage-column column-machine-name sortable" style="">
                    <a href="#">
                        <span>Machine Name</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
                <th scope="col" id="entries" class="manage-column column-entries sortable" style="">
                    <a href="#">
                        <span>Entries</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
                <th scope="col" id="date" class="manage-column column-date sortable" style="">
                    <a href="#">
                        <span>Modified</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
            </tr>
        </tfoot> 

        <tbody id="the-list"> 

            <?php
                $urledit = './admin.php?page=pods&amp;preview=edit-pod';
            ?>
            <tr id="pod-1" class="alternate" valign="top"> 
                <th scope="row" class="check-column"><input type="checkbox" name="pod[]" value="1" /></th> 
                <td class="pod-label column-label">
                    <strong>
                        <a class="row-label" href="<?php echo $urledit; ?>">FAQ</a>
                    </strong> 
                    <div class="row-actions">
                        <span class="edit">
                            <a href="<?php echo $urledit; ?>">Edit</a>
                             | 
                        </span>
                        <span class="delete">
                             <a class="submitdelete" title="Delete this Pod" href="#">Delete</a> 
                        </span>
                    </div> 
                </td>
                <td class="machine-name column-machine-name">
                    <a href="<?php echo $urledit; ?>">faq</a>
                </td> 
                <td class="entries column-entries">
                    <div class="post-entry-count-wrapper"> 
                        <a href="#" title="3 entries" class="post-entry-count">
                            <span class="entry-count">4</span>
                        </a>
                    </div>
                </td> 
                <td class="date column-date">
                    <abbr title="2010/12/02 2:13:15 PM">2010/12/02</abbr>
                </td>
            </tr> 

            <tr id="pod-2" class="" valign="top"> 
                <th scope="row" class="check-column"><input type="checkbox" name="pod[]" value="2" /></th> 
                <td class="pod-label column-label">
                    <strong>
                        <a class="row-label" href="<?php echo $urledit; ?>">Events</a>
                    </strong> 
                    <div class="row-actions">
                        <span class="edit">
                            <a href="<?php echo $urledit; ?>">Edit</a>
                             | 
                        </span>
                        <span class="delete">
                             <a class="submitdelete" title="Delete this Pod" href="#">Delete</a> 
                        </span>
                    </div> 
                </td>
                <td class="machine-name column-machine-name">
                    <a href="<?php echo $urledit; ?>">events</a>
                </td> 
                <td class="entries column-entries">
                    <div class="post-entry-count-wrapper"> 
                        <a href="#" title="3 entries" class="post-entry-count">
                            <span class="entry-count">124</span>
                        </a>
                    </div>
                </td> 
                <td class="date column-date">
                    <abbr title="2010/12/02 2:13:15 PM">2010/12/02</abbr>
                </td>
            </tr>

            <tr id="pod-1" class="alternate" valign="top"> 
                <th scope="row" class="check-column"><input type="checkbox" name="pod[]" value="3" /></th> 
                <td class="pod-label column-label">
                    <strong>
                        <a class="row-label" href="<?php echo $urledit; ?>">Team</a>
                    </strong> 
                    <div class="row-actions">
                        <span class="edit">
                            <a href="<?php echo $urledit; ?>">Edit</a>
                             | 
                        </span>
                        <span class="delete">
                             <a class="submitdelete" title="Delete this Pod" href="#">Delete</a> 
                        </span>
                    </div> 
                </td>
                <td class="machine-name column-machine-name">
                    <a href="<?php echo $urledit; ?>">team</a>
                </td> 
                <td class="entries column-entries">
                    <div class="post-entry-count-wrapper"> 
                        <a href="#" title="3 entries" class="post-entry-count">
                            <span class="entry-count">13</span>
                        </a>
                    </div>
                </td> 
                <td class="date column-date">
                    <abbr title="2010/12/02 2:13:15 PM">2010/12/02</abbr>
                </td>
            </tr>

        </tbody> 
    </table>
    <!-- /pods table -->

    <!-- bulk actions -->
    <div class="tablenav"> 
        <div class="alignleft actions"> 
            <select name="action"> 
                <option value="-1" selected="selected">Bulk Actions</option> 
                <option value="trash">Delete</option> 
            </select> 
            <input type="submit" name="doaction" id="doaction" class="button-secondary action" value="Apply"  /> 
        </div>
        <div class='tablenav-pages'>
            <span class="displaying-num">## items</span>
            <a class='first-page' title='Go to the first page' href='http://pods/wp-admin/edit.php?post_type=page'>&laquo;&laquo;</a> 
            <a class='prev-page' title='Go to the previous page' href='http://pods/wp-admin/edit.php?post_type=page&#038;paged=1'>&laquo;</a> 
            <input class='current-page' title='Current page' type='text' name='paged' value='1' size='1' /> of 
            <span class='total-pages'>#</span> 
            <a class='next-page' title='Go to the next page' href='http://pods/wp-admin/edit.php?post_type=page&#038;paged=2'>&raquo;</a> 
            <a class='last-page' title='Go to the last page' href='http://pods/wp-admin/edit.php?post_type=page&#038;paged=2'>&raquo;&raquo;</a>
        </div> 
        <br class="clear" /> 
    </div>
    <!-- /bulk actions -->

</div>