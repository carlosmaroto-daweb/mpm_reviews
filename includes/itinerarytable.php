<div>
    <table id="fieldset-one" class="my_itinerary">
        <thead>
            <tr>
                <th></th>
                <th>Days</th>
                <th>Places</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
                // Si tengo campos que se repitan creamos una fila para cada par de campos
                if($repeteable_fields) {
                   foreach($repeteable_fields as $field) {
            ?>
                        <tr>
                            <td><a class="remove-row" href=""><span class="dashicons dashicons-remove"></span></a></td>  
                            <td><input type="text" class="" name="days[]" value="<?php echo $field['days'];?>"></td>
                            <td><input type="text" class="" name="places[]" value="<?php echo $field['places'];?>"></td>
                            <td><a href="">|||</a></td>
                        </tr>
            <?php
                   }
                  // Si no tengo campos creamos una fila en blanco
                } else {
            ?>        
                <tr>
                    <td><a class="remove-row" href=""><span class="dashicons dashicons-remove"></span></a></td>  
                    <td><input type="text" class="" name="days[]" value=""></td>
                    <td><input type="text" class="" name="places[]" value=""></td>
                    <td><a href="">|||</a></td>
                </tr>    
            <?php        
                }
            ?>
        <!-- empty row for jQuery - LO VAMOS A USAR PARA CLONAR -->        
        <tr class="empty-row screen-reader-text">
            <td><a class="remove-row" href=""><span class="dashicons dashicons-remove"></span></a></td>  
            <td><input type="text" class="" name="days[]" value=""></td>
            <td><input type="text" class="" name="places[]" value=""></td>
            <td><a href="">|||</a></td>
        </tr>
              
        </tbody>
    </table>  
    <p class="add_button">
        <a id="add-row" class="add-row" href=""><span class="dashicons dashicons-plus-alt"></span> Add itinerary </a>
    </p>
</div>