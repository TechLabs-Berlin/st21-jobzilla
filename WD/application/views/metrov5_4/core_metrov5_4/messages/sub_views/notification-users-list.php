  
<?php
if(sizeof($user_list)>0){
    $user_lists=array_chunk($user_list, ceil(count($user_list) / 4));

    ?>

    <table width="100%" class="ml-5" id="table-listing-items" >

        <thead class="">
            <tr role="row" class="m-datatable__row" >

                <th class="w-25" style="width:25%"></th>
                <th class="w-25"></th>
                <th class="w-25"></th>
                <th class="w-25"></th>

            </tr>
        </thead>

        <tbody class="m-datatable__body custom-tbody m--font-bold">

            <tr class="" >
                <th class="w-25">
                    <?php if(isset($user_lists[0])): foreach ($user_lists[0] as $user_list) { ?>
                        <label class="m-checkbox"  title="<?php echo $user_list['email'] ?>">
                            <input data-name="<?php echo $user_list['first_name']." ".$user_list['last_name']; ?>" type="checkbox" class="people_checkbox" name="people[]" data-value="<?php echo $user_list['id']; ?>" value="<?php echo $user_list['id']; ?>"  <?php if (in_array($user_list['id'] , $list_of_people)) {echo "checked";}?>> 
                            <?php echo $user_list['first_name']." ".$user_list['last_name']; ?>
                            <span></span>
                        </label>
                    <?php } endif;?>
                </th>
                <th class="w-25">
                    <?php if(isset($user_lists[1])): foreach ($user_lists[1] as $user_list) { ?>
                        <label class="m-checkbox"  title="<?php echo $user_list['email'] ?>">
                            <input data-name="<?php echo $user_list['first_name']." ".$user_list['last_name']; ?>" type="checkbox" class="people_checkbox" name="people[]" data-value="<?php echo $user_list['id']; ?>" value="<?php echo $user_list['id']; ?>"  <?php if (in_array($user_list['id'] , $list_of_people)) {echo "checked";}?>> 
                            <?php echo $user_list['first_name']." ".$user_list['last_name']; ?>
                            <span></span>
                        </label>
                    <?php } endif;?>
                </th>
                <th class="w-25">
                    <?php if(isset($user_lists[2])): foreach ($user_lists[2] as $user_list) { ?>
                        <label class="m-checkbox"  title="<?php echo $user_list['email'] ?>">
                            <input data-name="<?php echo $user_list['first_name']." ".$user_list['last_name']; ?>" type="checkbox" class="people_checkbox" name="people[]" data-value="<?php echo $user_list['id']; ?>" value="<?php echo $user_list['id']; ?>"  <?php if (in_array($user_list['id'] , $list_of_people)) {echo "checked";}?>> 
                            <?php echo $user_list['first_name']." ".$user_list['last_name']; ?>
                            <span></span>
                        </label>
                    <?php } endif;?>
                </th>
                <th class="w-25">
                    <?php if(isset($user_lists[3])): foreach ($user_lists[3] as $user_list) { ?>
                        <label class="m-checkbox"  title="<?php echo $user_list['email'] ?>">
                            <input data-name="<?php echo $user_list['first_name']." ".$user_list['last_name']; ?>" type="checkbox" class="people_checkbox" name="people[]" data-value="<?php echo $user_list['id']; ?>" value="<?php echo $user_list['id']; ?>"  <?php if (in_array($user_list['id'] , $list_of_people)) {echo "checked";}?>> 
                            <?php echo $user_list['first_name']." ".$user_list['last_name']; ?>
                            <span></span>
                        </label>
                    <?php } endif;?>
                </th>

            </tr>

        </tbody>
    </table>


<?php }
?>