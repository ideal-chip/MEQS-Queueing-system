<div id='fullScreen'></div>
<div style="width: 500px;" id='transferDialog'>
    <div class="panel dialogInner">
        <div class="panel-heading">
            <div><?php echo $transferClient ?> </div>
            <div id="ticketNo" class="bg-gray-light box-red text-red font-bold font-md"></div>
        </div>
        <div class="panel-body" >
            <div class="pad-10">
                <label class="badge box-red text-whito"><input type='radio' name='tp' id='toCounter' checked="checked" onclick="switchOption('counters');"> <?php echo $distCounter ?>  </label>
                <label class="badge box-red text-whito"><input type='radio' name='tp' id='toCategory' onclick="switchOption('categories');"> <?php echo $distCategory ?></label>
            </div>
            <div class="bg-white">
                <select style='width:100%;' id='counters' class="form-control transfer-option">
                    <?php
                    foreach ($otherCounters as $counterItem) {
                        ?>
                        <option value='<?php echo $counterItem['counter_id'] ?>'><?php echo $counterItem['counter_name'] ?> </option>
                        <?php
                    }
                    ?>
                </select>
            </div>
            <div class="bg-white">
                <select style='width:100%; display: none;' id='categories' class="form-control transfer-option">
                    <?php
                    foreach ($categories as $categoryItem) {
                        ?>
                        <option value='<?php echo $categoryItem['category_id'] ?>'><?php echo $categoryItem['catName'] ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="panel-footer">
            <button class="btn btn-sm btn-primary" onclick='transfer();'><?php echo $ok ?> </button>
            <button class="btn btn-sm btn-primary" onclick='hideTransferDialog();'><?php echo $cancel ?>  </button>
        </div>
    </div>
</div>
