<div id="booking-modal" class="modal">
    <div class="modal-center modal-md">
        <div class="modal-content">
            <div class="modal-header bg-yellow-heavy pad-3">
                <button type="button" class="close text-whito marg3" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title text-whito"><?php echo $followupCards ?></h4>
            </div>
            <div class="modal-body bg-white-gray text-gray">
                <div id="book-form" class="pad-10 relative" >
                    <div id="booking-form-con" style="display: block;">
                        <div id="ajax-error" style="display: none;" class="alert alert-danger s-60 pad-10 marg-v-10 center-block">
                            <div class ="no-pad no-marg text-uppercase"><strong>Errors</strong></div>
                            <ul id="ajax-error-list" class="list-unstyled small">
                                <li class = "list-group-item"></li>
                            </ul>
                        </div>
                        <form id="booking-form" class="form-horizontal bg-white round-10 pad-10" >
                            <fieldset>
                                <div class="form-group form-group-sm" >
                                    <label for="client-name" class="col-lg-2 control-label"><?php echo $clientName ?></label>
                                    <div class="col-lg-10">
                                        <input class="form-control  form-ok" id="client-name" name="client-name" placeholder="<?php echo $clientName ?>" type="text">
                                    </div>
                                </div>
                                <div class="form-group form-group-sm">
                                    <label for="phone-number" class="col-lg-2 control-label"><?php echo $phoneNumber ?></label>
                                    <div class="col-lg-10">
                                        <input class="form-control form-ok" id="phone-number" name="phone-number" placeholder="<?php echo $phoneNumber ?>" type="text">
                                    </div>
                                </div>
                                <div class="form-group form-group-sm">
                                    <label for="category-id" class="col-lg-2 control-label"><?php echo $mainService ?></label>
                                    <div class="col-lg-10">
                                        <select onchange="updateSubServices(this.value, 2)" class="form-control line-1-5 bg-white-gray" name="category-id" id="category-id">
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
                                <div class="form-group form-group-sm">
                                    <label for="subcategory-id" class="col-lg-2 control-label"><?php echo $subService ?></label>
                                    <div class="col-lg-10">
                                        <select onchange="updateWaitTime(this)" class="form-control line-1-5 bg-white-gray" name="subcategory-id" id="subcategory-id">

                                        </select>
                                    </div>
                                </div>
                                <div class="form-group form-group-sm">
                                    <label for="sub_fones" class="col-lg-2 control-label"><?php echo $subFone ?></label>
                                    <div class="col-lg-10">
                                        <select class="form-control line-1-5 bg-white-gray" name="sub_fones" id="sub_fones">
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group form-group-sm">
                                    <div class="col-lg-12 text-right">
                                        <button onclick="SendBookingForm()" type="button" class="btn btn-primary"><?php echo $edit ?></button>
                                    </div>
                                </div>
                                <input type="hidden" id="ajaxMode" name="ajaxMode" value="update"/>
                                <input type="hidden" id="followup-id" name="followup-id" value="0"/>
                                <input class="" type="hidden" id="event-id" name="event-id" value="0"/>
                                <input type="hidden" id="clerk-id" name="clerk-id" value="<?php echo $clerkID; ?>" />
                                <input type="hidden" id="lang-id" name="lang-id" value="<?php echo $lang; ?>" />
                            </fieldset>
                        </form>
                    </div>
                    <div id="booking-preview" style="display: none;">
                        <div class="row no-print bg-white pad-5">
                            <button class="btn btn-sm btn-warning pull-left" onclick="printElement('booking-preview')" type="button" ><i class="fa fa-print"></i> <?php echo $print ?></button>
                            <!--<button class="btn btn-sm btn-default pull-right" onclick="resetAndShowForm()"> <?php echo $cancel ?> </button>-->
                            <button class="fedit-btn btn btn-sm btn-primary pull-right"  onclick="editBookingForm(this.value)" value="" type="button" > <?php echo $edit ?> </button>
                        </div>
                        <div class="pad-5">
                            <img src="../files/logos/env-logo.png" alt=""/>
                        </div>
                        <div class="border-btm pad-20 h3">
                            <?php echo $followupCard; ?>
                        </div>
                        <hr>
                        <div class="row marg-h-10">
                            <div class="inline-block s-30">
                                <div class="well well-sm">
                                    <div>
                                        <i class="fa fa-calendar pad-h-10"></i>
                                        <span class="badge no-radius s-80"><?php echo $dateTime; ?>:</span>
                                    </div>
                                    <div>
                                        <span id="f-datetime"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="inline-block s-30">
                                <div class="well well-sm">
                                    <div>
                                        <i class="fa fa-user-circle pad-h-10"></i>
                                        <span class="badge no-radius s-80"><?php echo $clerkNameLang ?>:</span>
                                    </div>
                                    <div>
                                        <span id="f-clerkName"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="inline-block s-30">
                                <div class="well well-sm">
                                    <div>
                                        <i class="fa fa-list-alt pad-h-10"></i>
                                        <span class="badge no-radius s-80"><?php echo $serialNo; ?>:</span>
                                    </div>
                                    <div>
                                        <span id="f-serialNo"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row marg-h-10 no-align">
                            <div class="row marg-h-10">
                                <div class="inline-block s-25 control-label font-bold"><?php echo $clientName; ?></div>
                                <div class="inline-block s-70">
                                    <div id="f-clientName" class="well well-sm"></div>
                                </div>
                            </div>
                            <div class="row marg-h-10">
                                <div class="inline-block s-25 control-label font-bold"><?php echo $phoneNumber; ?></div>
                                <div class="inline-block s-70">
                                    <div id="f-phoneNumber" class="well well-sm"></div>
                                </div>
                            </div>
                            <div class="row marg-h-10">
                                <div class="inline-block s-25 control-label font-bold"><?php echo $directorate; ?></div>
                                <div class="inline-block s-70">
                                    <div id="f-mainService" class="well well-sm"></div>
                                </div>
                            </div>
                            <div class="row marg-h-10">
                                <div class="inline-block s-25 control-label font-bold"><?php echo $serviceType; ?></div>
                                <div class="inline-block s-70">
                                    <div id="f-subService" class="well well-sm"></div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="border-top pad-20 no-align small">
                            <ul class="list-group">
                                <i class="list-group-item pad-3-10"><?php echo $followupFootnote1; ?></i>
                                <i class="list-group-item pad-3-10"><?php echo $followupFootnote2; ?></i>
                                <i class="list-group-item pad-3-10"><?php echo $followupFootnote3; ?> <span id="f-waittime" class="well well-sm pad-3-10">5</span> <?php echo $followupFootnote4; ?></i>
                            </ul>
                        </div>
                        <div class="row no-print bg-white pad-5">
                            <button class="btn btn-sm btn-warning pull-left" onclick="printElement('booking-preview')" type="button" ><i class="fa fa-print"></i> <?php echo $print ?></button>
                            <button class="fedit-btn btn btn-sm btn-primary pull-right" onclick="editBookingForm(this.value)" value="" type="button" > <?php echo $edit ?> </button>

                        </div>
                    </div>
                </div>
                <input type='hidden' id="row-id" name="row-id" value="0"/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $close ?></button>
            </div>
        </div>
    </div>
</div>
