import * as data from './_data';
import * as Lib from '../_helpers';

export function add_map()
{
    var _map = null;
    if(document.getElementById('map') )
    {
        _map = Lib._create_map('map',data.marker_data_1);
    }
    return _map;
}

