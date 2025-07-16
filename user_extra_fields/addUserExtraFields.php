<?php


function save_custom_user_profile_fields($user_id){
    # again do this only if you can
    if(!current_user_can('manage_options'))
        return false;
 
    # save my custom field
    update_user_meta($user_id, 'address', $_POST['address']);
    update_user_meta($user_id, 'province', $_POST['province']);
    update_user_meta($user_id, 'phone1', $_POST['phone1']);
    update_user_meta($user_id, 'phone2', $_POST['phone2']);
    update_user_meta($user_id, 'fax', $_POST['fax']);
    update_user_meta($user_id, 'city', $_POST['city']);
}
add_action('user_register', 'save_custom_user_profile_fields');
add_action('profile_update', 'save_custom_user_profile_fields');

function custom_user_profile_fields($user){
    if(is_object($user)):
        $address = esc_attr( get_the_author_meta( 'address', $user->ID ) );
        $province = esc_attr( get_the_author_meta( 'province', $user->ID ) );
        $phone1 = esc_attr( get_the_author_meta( 'phone1', $user->ID ) );
        $phone2 = esc_attr( get_the_author_meta( 'phone2', $user->ID ) );
        $fax = esc_attr( get_the_author_meta( 'fax', $user->ID ) );
        $city = esc_attr( get_the_author_meta( 'city', $user->ID ) );
    else:
        $province = null;
    endif;
    ?>
    <h3>Extra</h3>
    <table class="form-table">
    <tr>
     <th><label for="phone1">Phone 1</label></th>
            <td>
            <input type='text' name='phone1' id='phone1' class='phone1 regular-text code' value='<?= $phone1??NULL ?>' />
               
           
            </td>
        
        </tr>
        </tr>
        <th><label for="phone2">Phone 2</label></th>
            <td>
            <input type='text' name='phone2' id='phone2' class='phone2 regular-text code' value='<?= $phone2??NULL ?>' />
               
            
            </td>
        </tr>
        <tr>
            <th><label for="fax">Fax</label></th>
            <td>
            <input type='text' name='fax' id='fax' class='fax regular-text code' value='<?= $fax??NULL ?>' />
               
            
            </td>
        </tr>
        <tr>
            <th><label for="address">Address</label></th>
            <td>
            <textarea  name='address' id='address' class='address regular-text code' cols="30" rows="10"><?= $address??NULL ?></textarea>
            
            
            </td>
        </tr>

        <tr>
            <th><label for="province">Province</label></th>
            <td>
               
                <select name='province' id='province' class='regular-text code'>
                <option value ='' >Select Province</option>
                <option value ='Punjab'  <?= ($province == 'Punjab')?'selected':NULL;?> >Punjab</option>
                <option value ='Sindh' <?= ($province == 'Sindh')?'selected':NULL;?> >Sindh</option>
                <option value ='Khyber Pakhtunkhwa'  <?= ($province == 'Khyber Pakhtunkhwa')?'selected':NULL;?> >Khyber Pakhtunkhwa</option>
                <option value ='Balochistan'  <?= ($province == 'Balochistan')?'selected':NULL;?> >Balochistan</option>
                <option value ='Islamabad Capital Territory'  <?= ($province == 'Islamabad Capital Territory')?'selected':NULL;?> >Islamabad Capital Territory</option>
                <option value ='Gilgit-Baltistan'  <?= ($province == 'Gilgit-Baltistan')?'selected':NULL;?> >Gilgit-Baltistan</option>
                <option value ='Azad Jammu and Kashmir'  <?= ($province == 'Azad Jammu and Kashmir')?'selected':NULL;?> >Azad Jammu and Kashmir</option>
                </select>
                <br />
                <span class="description">Where are you?</span>
            </td>
            </tr>
            <tr>

            <th><label for="city">City</label></th>
            <td>
            <input type='text' name='city' id='city' class='city regular-text code' value='<?= $city??NULL ?>' />
               
            
            </td>
        </tr>
    </table>
<?php
}
add_action( 'show_user_profile', 'custom_user_profile_fields' );
add_action( 'edit_user_profile', 'custom_user_profile_fields' );
add_action( "user_new_form", "custom_user_profile_fields" );
