<?php

require_once __DIR__.'/../../config/permissions.php';

if(hasRole('Administrator')){

    require __DIR__.'/sidebars/admin.php';

}
elseif(hasRole('Chairperson')){

    require __DIR__.'/sidebars/chairperson.php';

}
elseif(hasRole('Treasurer')){

    require __DIR__.'/sidebars/treasurer.php';

}
elseif(hasRole('Secretary')){

    require __DIR__.'/sidebars/secretary.php';

}
else{

    require __DIR__.'/sidebars/member.php';

}