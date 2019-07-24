import * as data from './_data';
import * as Lib from '../_helpers';

export function add_map()
{
    var _map = null;
    if(document.getElementById('map-2') )
    {
        _map = Lib._create_map('map-2',data.marker_data_2);
    }
    return _map;
}
