<?php 
namespace MW_STATIC\Admin;

use MW_STATIC\Inc\Services\Services;
use MW_STATIC\Inc\Export\Multi_Export;
use MW_STATIC\Inc\Export\Single_Export;
use MW_STATIC\Inc\License\Active_License;
use MW_STATIC\Inc\License\Deactive_License;

class Admin_Init{
    public function __construct(private Services $service) {
        $service->get(Dashboard::class);
        
        $service->get(Bulk_Export::class);
        $service->get(Action_Buttons::class);


        $service->get(Single_Export::class);
        $service->get(Multi_Export::class);

        $service->get(Active_License::class);
        $service->get(Deactive_License::class);
    }
}