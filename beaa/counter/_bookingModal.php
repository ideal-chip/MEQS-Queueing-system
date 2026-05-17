<div id="booking-modal" class="modal">
    <div class="modal-center modal-md">
        <div class="modal-content">
            <div class="modal-header bg-yellow-heavy pad-3">
                <button type="button" class="close text-whito marg3" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title text-whito"><?php echo $followupCards ?></h4>
            </div>
            <div class="modal-body bg-white-gray text-gray">
                <ul class="nav nav-tabs nav-justified">
                    <li class="active"><a data-toggle="tab" href="#booking"><i class="fa-box fa fa-book"></i> <?php echo $issueFollowupCard ?></a></li>
                    <li><a data-toggle="tab" href="#papers"><?php echo $requiredPapers ?></a></li>
                    <li><a data-toggle="tab" href="#followups"><?php echo $followupCards ?></a></li>
                    <li><a data-toggle="tab" href="#morepapers"><?php echo $morepapers ?></a></li>
                </ul>
                <div class="tab-content">
                    <div id="booking" class="tab-pane fade in active" >
                        <div id="book-form" class="pad-10 relative" >
                            <!--                                    <div class="fullscreen"></div>
                                                                <div class="overly vertical-t-bottom"> Choose a ticket first</div>-->
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
                                                <select onchange="updateSubServices(this.value)" class="form-control line-1-5 bg-white-gray" name="category-id" id="category-id">
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
<!--                                                    <option value="162">162</option>
                                                    <option value="143">143</option>
                                                    <option value="152">152</option>
                                                    <option value="160">160</option>
                                                    <option value="105">105</option>
                                                    <option value="104">104</option>-->
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group form-group-sm">
                                            <div class="col-lg-12 text-right">
                                                <button onclick="emptyForm()" type="reset" class="btn btn-default"><?php echo $clear ?></button>
                                                <button onclick="SendBookingForm()" type="button" class="btn btn-primary"><?php echo $issue ?></button>
                                            </div>
                                        </div>
                                        <input type="hidden" id="ajaxMode" name="ajaxMode" value="add"/>
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
                                    <button class="btn btn-sm btn-default pull-right" onclick="resetAndShowForm()"> <?php echo $cancel ?> </button>
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
                                        <i class="list-group-item pad-3-10"><?php echo $followupFootnote5; ?> <span id="sub_fone" class="well well-sm pad-3-10">123</span></i>
                                    </ul>
                                </div>
                                <div class="row no-print bg-white pad-5">
                                    <button class="btn btn-sm btn-warning pull-left" onclick="printElement('booking-preview')" type="button" ><i class="fa fa-print"></i> <?php echo $print ?></button>
                                    <button class="btn btn-sm btn-default pull-right" onclick="resetAndShowForm()"> <?php echo $cancel ?> </button>
                                    <button class="fedit-btn btn btn-sm btn-primary pull-right" onclick="editBookingForm(this.value)" value="" type="button" > <?php echo $edit ?> </button>
                                </div>
                                <!--                                <div class="row marg-10 no-print">
                                                                    <div class="col-md-6">
                                                                        <button onclick="printElement('booking-preview')" type="button" class="btn btn-warning"><i class="fa fa-print"></i> <?php echo $print ?></button>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <button class="btn btn-default" onclick="resetAndShowForm()"><?php echo $cancel ?></button>
                                                                        <button id="fedit-btn" onclick="editBookingForm(this.value)" value="" type="button" class="btn btn-primary"><?php echo $edit ?></button>
                                                                        <input id="fedit-btn" type="hidden" value="" />
                                                                    </div>
                                                                </div>-->
                            </div>
                        </div>
                    </div>
                    <div id="papers" class="tab-pane fade">
                        <h5><?php echo $requiredPapers ?></h5>
                        <div class="row marg-10">
                            <div class="inline-block s-35">
                                <div class="well well-sm">
                                    <div>
                                        <span class="badge no-radius s-100"><?php echo $mainService; ?>:</span>
                                    </div>
                                    <div>
                                        <select onchange="updateSubServices(this.value, 'papers')" class="form-control form-control-md bg-white-gray" name="category-id-papers" id="category-id-papers">
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
                            </div>
                            <div class="inline-block s-35">
                                <div class="well well-sm">
                                    <div>
                                        <span class="badge no-radius s-100"><?php echo $subService; ?>:</span>
                                    </div>
                                    <div>
                                        <select onchange="updatePapersBySubcategory(this.id)"  class="form-control form-control-md bg-white-gray" name="subcategory-id-papers" id="subcategory-id-papers">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="inline-block s-20 vertical-t-bottom">
                                <div class="">
                                    <!--<button class="btn btn-default" onclick="updatePapersBySubcategory('subcategory-id-papers')"><?php echo $update ?></button>-->
                                    <button onclick="printElement('req-papers-preview')" type="button" class="btn btn-warning"><i class="fa fa-print"></i> <?php echo $print ?></button>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white pad-10">
                            <div id="req-papers-preview" class="no-align"></div>
                        </div>
                        <div class="row marg-10 no-print">
                            <div class="pull-right">
                                <button onclick="printElement('req-papers-preview')" type="button" class="btn btn-warning"><i class="fa fa-print"></i> <?php echo $print ?></button>
                            </div>
                        </div>
                    </div>
                    <div id="followups" class="tab-pane fade">
                        <h5><?php echo $followupCards ?> <span id="totalItems" class="badge">0</span></h5>
                        <div class="pad-3">
                            <ul class="pagination">
                                <li class="page-item">
                                    <button id="nav-first" type="button"  onclick="getNextPrevPageFollowups('first')" class="btn btn-default btn-sm"><?php echo $firstPage ?></button>
                                </li>
                                <li class="page-item ">
                                    <button id="nav-prev" type="button" onclick="getNextPrevPageFollowups('prev')" class="btn btn-default btn-sm"><i class="fa fa-arrow-right"></i></button>
                                </li>
                                <li class="page-item">
                                    <div id="list-page" class="inline-block bg-blue-2 pad-3-10 sh-gray center-block text-whito"></div>
                                </li>
                                <li class="page-item">
                                    <button id="nav-next" type="button" onclick="getNextPrevPageFollowups('next')" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i></button>
                                </li>
                                <li class="page-item">
                                    <button id="nav-last" type="button" onclick="getNextPrevPageFollowups('last')" class="btn btn-default btn-sm"><?php echo $lastPage ?></button>  
                                </li>
                            </ul>
                        </div>
                        <div id="followups-list" class="table-responsive">
                            <table id="followups-table" class="table table-condensed table-responsive small table-close s-100 bg-dark-gray text-whito">
                            </table>
                        </div>
                        <!--<div id="list-page" class="bg-blue-2 pad-3-10 sh-gray center-block text-whito"></div>-->
                    </div>
                    <div id="morepapers" class="tab-pane fade">
                        <h5><?php echo $morepapers ?> </h5>
                        <div class="bg-gray-light pad-10 round-10">
                            <table class="table table-condensed table-close" >
                                <tr class="bg-primary text-center-th">
                                    <th><?php echo getTextValue("fileName", $lang) ?>  </th>
                                    <th><?php echo getTextValue("type", $lang) ?>  </th>
                                    <th><?php echo getTextValue("fileSize", $lang) ?>  </th>
                                </tr>
                                <?php
                                foreach ($otherFiles as $other) {
                                    $file_type = strtolower(pathinfo($other)['extension']);
                                    ?>
                                    <tr>
                                        <td>
                                            <a class="btn btn-default btn-xs s-100 text-gray" target="_plank" href='<?php echo $uploadsPath . $other ?>'><?php echo $other ?></a>
                                        </td>
                                        <td class="text-gray" ><?php echo $file_type ?>  </td>
                                        <td class="text-gray" ><?php echo human_filesize(filesize($uploadsPath . $other)) ?>  </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $close ?></button>
            </div>
        </div>
    </div>
</div>
