

<form id="search-form" class=" bg-white round-10 pad-10" >
    <input id="page" name="page" type="hidden" value="<?php echo $page ?>" />
    <input id="adv" name="adv" type="hidden" value="<?php echo $adv ?>" />
    <div class="row">
        <div class="pull-left">
            <a class="btn btn-default round-50-sh"  href="./search.php"><i class="fa fa-recycle font-lg text-green-dark"></i> <?php echo "" ?></a>
        </div>
        <div class="form-group col-md-6">
            <div class="input-group  marg3">
                <div class="input-group-btn">
                    <button class="btn btn-primary round-5-sh pad-h-30" type="submit">
                        <?php echo $search ?> <i class="glyphicon glyphicon-search"></i>
                    </button>
                </div>
                <input id="q" name="q" value="<?php echo $searchQry ?>" type="text" class="form-control pad-h-10 box-block"  placeholder="<?php echo $searchOptions; ?>">

            </div>
        </div>
        <div class="pull-right">
            <a class="btn btn-warning btn-xs" id="show-advanced" href="javascript:void(0);"><?php echo $advancedSearch ?></a> |
            <a class="btn btn-success btn-xs" onclick="exportData('q-result');" href="javascript:void(0)"><i class="fa fa-file-excel-o pad-h-5"></i> <?php echo $excelExport ?> </a> | 
            <a class="btn btn-info btn-xs" onclick="printSeachResult('q-result')" href="javascript:void(0)"><i class="glyphicon glyphicon-print"></i> <?php echo $print ?></a>
        </div>
    </div>
    <div id="advanced-search" class="row inside-box round-10 marg3" style="display: <?php echo $advShown ?>;">
        <div class="form-group form-group-sm col-md-4">
            <div class="marg3  pad-5">
                <div class="text-center bg-white-gray"><?php echo $lang_categories ?></div>
                <div class="input-group sh-blue marg-5 round-5">
                    <span class="input-group-addon bg-white-gray pad-5"><i class="fa fa-book"></i> <?php echo $mainService ?></span>
                    <select onchange="updateSubServices(this.value);" class="form-control" name="fcategory-id" id="fcategory-id">
                        <option value="0" <?php ($req_category_id == 0 ) ? 'selected' : ''; ?> ><?php echo "-" ?></option>
                        <?php
                        foreach ($categories as $categoryItem) {
                            $selected = ($req_category_id == $categoryItem['category_id']) ? 'selected' : '';
                            ?>
                            <option value='<?php echo $categoryItem['category_id'] ?>' <?php echo $selected ?> ><?php echo $categoryItem['catName'] ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="input-group sh-blue marg-5 round-5">
                    <span class="input-group-addon bg-white-gray pad-5"><i class="fa fa-book"></i> <?php echo $subService ?></span>
                    <select class="form-control" name="fsubcategory-id" id="fsubcategory-id">
                        <option value="0" <?php ($subCategory_id == 0 ) ? 'selected' : ''; ?> ><?php echo "-" ?></option>
                        <?php
                        foreach ($subCateories as $subcategoryItem) {
                            $selected = ($subCategory_id == $subcategoryItem['subcategory_id']) ? 'selected' : '';
                            ?>
                            <option value='<?php echo $subcategoryItem['subcategory_id'] ?>' <?php echo $selected ?> ><?php echo $subcategoryItem['subcategory_name'] ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="">
                    <div class="text-center bg-white-gray"><?php echo $lang_clerks ?></div>
                    <div class="input-group  sh-blue marg-5 round-5">
                        <span class="input-group-addon bg-white-gray pad-5"><i class="fa fa-user"></i></span>
                        <select class="form-control" name="fclerk-id" id="fclerk-id">
                            <option value="0" <?php ($clerk_id == 0 ) ? 'selected' : ''; ?> ><?php echo "-" ?></option>
                            <?php
                            foreach ($clerks as $clerkItem) {
                                if (!empty(trim($clerkItem['clerk_fullname']))) {
                                    $clerkName = $clerkItem['clerk_fullname'] . " (" . $clerkItem['clerk_name'] . ")";
                                } else {
                                    $clerkName = $clerkItem['clerk_name'];
                                }
                                $selected = ($clerk_id == $clerkItem['clerk_id']) ? 'selected' : '';
                                ?>
                                <option value='<?php echo $clerkItem['clerk_id'] ?>' <?php echo $selected ?> ><?php echo $clerkName ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>

                </div>
            </div>
        </div>
        <div class="form-group form-group-sm col-md-2 ">
            <div class="marg3 text-left  pad-5">
                <div class="text-center bg-white-gray"><?php echo $type ?></div>
                <div class="radio pad-5 bg-warning round-5 marg3">
                    <label class="label-fix">
                        <input name="type-option" id="type-none" value="0" <?php echo $typeOption == 0 ? 'checked' : ''; ?> type="radio">
                        <?php echo $notImportant ?>
                    </label>
                </div>
                <div class="radio pad-5 bg-warning round-5 marg3">
                    <label class="label-fix">
                        <input name="type-option" id="type-ready" <?php echo $typeOption == 1 ? 'checked' : ''; ?> value="1" type="radio">
                        <?php echo $processed ?> 
                    </label>
                </div>
                <div class="radio pad-5 bg-warning round-5 marg3">
                    <label class="label-fix">
                        <input name="type-option" id="type-not-ready" <?php echo $typeOption == 2 ? 'checked' : ''; ?> value="2" type="radio">
                        <?php echo $notProcessed ?>
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group form-group-sm col-md-3">
            <div class="marg3 pad-5 text-left">
                <div class="text-center bg-white-gray"><?php echo $selectDates ?></div>
                <div class="checkbox pad-5 bg-warning round-5 marg3">
                    <label class="label-fix">
                        <input name="create_date_op" id="create_date_op" <?php echo!empty($createDateOption) ? 'checked' : ''; ?> style="width: auto !important;" type="checkbox">
                        <?php echo $created ?> 
                    </label>
                </div>
                <div class="form-inline ">
                    <div class="input-group sh-blue marg3 round-5 ">
                        <span class="input-group-addon bg-white-gray pad-5"><i class="fa fa-calendar"></i> <?php echo $from ?></span>
                        <input  id="create_from_date" name="create_from_date" maxlength="0" class="date-pick form-control no-radius text-box single-line pad-h-5 font-md" placeholder="choose date" value="<?php echo $createDateFrom; ?>" type="text">
                    </div>
                    <div class="input-group sh-blue marg3 round-5 ">
                        <span class="input-group-addon bg-white-gray pad-5"><i class="fa fa-calendar"></i> <?php echo $to ?></span>
                        <input  id="create_to_date" name="create_to_date" maxlength="0" class="date-pick form-control no-radius text-box single-line pad-h-5 font-md" placeholder="choose date" value="<?php echo $createDateTo; ?>" type="text">
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group form-group-sm col-md-3">
            <div class="marg3 pad-5 text-left">
                <div class="text-center bg-white-gray"><?php echo $selectDates ?></div>
                <div class="checkbox pad-5 bg-warning round-5 marg3">
                    <label class="label-fix">
                        <input  name="done_date_op" id="done_date_op" <?php echo!empty($doneDateOption) ? 'checked' : ''; ?> style="width: auto !important;" type="checkbox">
                        <?php echo $finished ?>
                    </label>
                </div>
                <div class="form-inline ">
                    <div class="input-group sh-blue marg3 round-5 ">
                        <span class="input-group-addon bg-white-gray pad-5"><i class="fa fa-calendar"></i> <?php echo $from ?></span>
                        <input  id="done_from_date" name="done_from_date" maxlength="0" class="date-pick form-control no-radius text-box single-line pad-h-5 font-md" placeholder="choose date" value="<?php echo $doneDateFrom; ?>" type="text">
                    </div>
                    <div class="input-group sh-blue marg3 round-5 ">
                        <span class="input-group-addon bg-white-gray pad-5"><i class="fa fa-calendar"></i> <?php echo $to ?></span>
                        <input  id="done_to_date" name="done_to_date" maxlength="0" class="date-pick form-control no-radius text-box single-line pad-h-5 font-md" placeholder="choose date" value="<?php echo $doneDateTo; ?>" type="text">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <button class="btn btn-primary btn-sm round-5-sh pad-10 pad-h-30 marg-v-20" type="submit">
                <?php echo $search ?> <i class="glyphicon glyphicon-search"></i>
            </button>
            <a class="btn btn-default round-50-sh marg-h-10"  href="./search.php">
                <i class="fa fa-recycle font-lg text-green-dark"></i>
                <?php echo "" ?>
            </a>
        </div>

    </div>

</form>