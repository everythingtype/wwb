require('./_custom_animate_waypoint');
require('./_maps/_init');
require('./_progress/_index');

jQuery(document).ready(function(){

    //to stop the panels from fully closing on maps area
    $('.inner-panel-panel-heading a').on('click',function(e){
        var _panel = $(this).attr('href');
        if($(this).parents('.panel').children(_panel).hasClass('in')){
            e.stopPropagation();
            e.preventDefault();
        }
    });

    var _form = `<form action="http://ww2.womensworldbanking.org/l/123612/2017-05-31/3bw46l" method="post" class="form-inline clearfix">
                <div class="clearfix top">
                    <div class="form-group">
                        <select class="form-control" name="SALUTATION">[add a blank placeholder here]: can be a dash
                            <option value="Ms.">Ms.</option>
                            <option value="Mrs.">Mrs.</option>
                            <option value="Mr.">Mr.</option>
                            <option value="Dr.">Dr.</option>
                            <option value="Prof.">Prof.</option>
                        </select>
                        <input class="form-control" type="text" name="FNAME" placeholder="First Name" required="required">
                        <input class="form-control" type="text" name="LNAME" placeholder="Last Name" required="required">
                    </div>
                </div>
                <div class="clearfix bottom">
                    <div class="form-group">
                        <input class="form-control" type="email" name="EMAIL" placeholder="Email address" required="required">
                        <select name="AFFILIATION" class="form-control">
                            <option value="Financial Institutions">Bank</option>
                            <option value="Financial Institutions">Microfinance Institution</option>
                            <option value="Industry Initiatives & NGOs">Nonprofit</option>
                            <option value="Bilaterals & Multilaterals">Government Donor</option>
                            <option value="Corporates & Foundations">Private Sector Donor</option>
                            <option value="Corporates & Foundations">Foundation</option>
                            <option value="Investors"> Investor</option>
                            <option value="Regulators & Policymakers">Regulator</option>
                            <option value="Regulators & Policymakers">Policymaker</option>
                            <option value="Consultant (Individual/ Firm)">Consultant</option>
                            <option value="Mobile Network Operators">Mobile Network Operator</option>
                            <option value="Financial Technology Company">Fintech</option>
                            <option value="Academic Institution or Researcher">Academic Institution or Researcher</option>
                            <option value="Individual Solicitation"> Other</option>
                        </select>
                        <div style="position:absolute; left:-9999px; top: -9999px;">
                            <label for="pardot_extra_field">Comments</label>
                            <input class="form-control" type="text" id="pardot_extra_field" name="pardot_extra_field">
                        </div>
                        <input type="submit" class="btn btn-default submit" value="Sign Up">
                    </div>
                </div>
            </form>`;

    $('.show-form').popover({
        template : `<div class="popover subscribe-popover" role="tooltip" style="width: auto;max-width: 100%;">
                <div class="arrow"></div>
                <!--'<h3 class="popover-title"></h3>-->
                <div class="content clearfix">
                    ${_form}
                </div>
            </div>`
    });

    $('.scroll_to').localScroll();
    $('.show-form').on('hidden.bs.popover',function(){
        $(this).removeClass('show-popover');
    });
    $('.show-form').on('shown.bs.popover',function(){
        $(this).addClass('show-popover');
    });
    $('.show-form').click(function(){
        $(this).popover('toggle');
        return false;
    });
});