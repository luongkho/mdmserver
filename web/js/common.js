/**
 * @author: Merlin Mai
 */
;
function my_alert(msg, status, callback) {
    var statusMsg;
    if(status && status != 'error'){
        statusMsg = status;
    }
    BootstrapDialog.show({
        message: msg,
        type: statusMsg,
        cssClass: 'overtop-modal',
        onhide : function (dialogRef) {
            if (callback != null) callback();
        },
        buttons: [{
            label: 'OK',
            action: function(dialogItself){
                dialogItself.close();
            }
        }]
    });
}
function my_confirm(msg, ok, notok) {
    BootstrapDialog.show({
        message: msg,
        cssClass: 'overtop-modal',
        buttons: [{
            label: 'Yes',
            cssClass: 'green',
            action: function(dialogItself){
                if (ok != null) ok();
                dialogItself.close();
            }
        }, {
            label: 'No',
            action: function(dialogItself){
                if (notok != null) notok();
                dialogItself.close();
            }
        }]
    });
}

/**
 * Convert from server timezone to local timezone.
 * @param {String} current_time
 * @returns {String}
 */
function get_local_time(current_time, fullTime){
    var localTime  = moment.utc(current_time).toDate();
    if(fullTime){
        localTime = moment(localTime).format('YYYY-MM-DD HH:mm:ss');
    }else{
        localTime = moment(localTime).format('YYYY-MM-DD');
    }
//    return current_time + "\n<br>" + localTime + "\n<br>";
    return localTime;
}

function display_local_time(current_time, fullTime){
    var display_time = get_local_time(current_time,fullTime);
    document.write(display_time);
}

/*THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL Galeola S.r.l.( www.galeola.it )(Maxim Postoronca) OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 *  DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 *  OTHER DEALINGS IN THE SOFTWARE.*/
if ( typeof $.fn.dataTable == "function" && typeof $.fn.dataTableExt.fnVersionCheck == "function" && $.fn.dataTableExt.fnVersionCheck('1.9.2')/*older versions should work too*/ )
{
    $.fn.dataTableExt.oApi.clearSearch = function ( oSettings )
    {
        var table = this;

        //any browser, must include your own file
        //var clearSearch = $('<img src="/images/delete.png" style="vertical-align:text-bottom;cursor:pointer;" alt="Delete" title="Delete"/>');

        //no image file needed, css embedding must be supported by browser
        var clearSearch = $('<i class="datatable-clear-search-btn fa fa-times"></i>');
        $(clearSearch).click( function ()
        {
            table.fnFilter('');
        });
        $(oSettings.nTableWrapper).find('div.dataTables_filter').append(clearSearch);
        $(oSettings.nTableWrapper).find('div.dataTables_filter label').css('margin-right', '-16px');//16px the image width
        $(oSettings.nTableWrapper).find('div.dataTables_filter input').css('padding-right', '16px');
    }

    //auto-execute, no code needs to be added
    $.fn.dataTable.models.oSettings['aoInitComplete'].push( {
        "fn": $.fn.dataTableExt.oApi.clearSearch,
        "sName": 'whatever'
    } );
}