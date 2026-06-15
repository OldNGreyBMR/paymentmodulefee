<?php
// ----
// Admin-level installation script for the "encapsulated" Payment Module Fee plugin for Zen Cart, by oldngrey.
// Copyright (C) 2026, Zen Cart team, OldNGrey BMH.
//
// Last updated: v2.0.4 (new)
//
use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    private string $configGroupTitle = 'Payment Module Fee';

    protected function executeInstall()
    {
        if (!$this->purgeOldFiles()) {
            return false;
        }

        return true;
    }

    // -----
    // Not used, initially, but included for the possibility of future upgrades!
    //
    protected function executeUpgrade($oldVersion)
    {
    }

    protected function executeUninstall()
    {
    }

    protected function purgeOldFiles(): bool
    {
        // -----
        // First, look for and remove the non-encapsulated version's 
        // files.
        //
        $files_to_check = [
            DIR_FS_CATALOG . 'includes/languages/english/modules/order_total/lang.ot_paymentmodulefee.php',
            DIR_FS_CATALOG . 'includes/languages/english/modules/order_total/ot_paymentmodulefee.php',
            
            DIR_FS_CATALOG . 'includes/modules/order_total/ot_paymentmodulefee.php',
            
            DIR_FS_CATALOG . 'includes/templates/YOUR_TEMPLATE/auto_loaders/loader_ot_paymentmodulefee.php',
            DIR_FS_CATALOG . 'includes/templates/YOUR_TEMPLATE/jscript/jquery/jquery_ot_paymentmodulefee.js',
            DIR_FS_CATALOG . 'includes/templates/template_default/auto_loaders/loader_ot_paymentmodulefee.php',
            DIR_FS_CATALOG . 'includes/templates/template_default/jscript/jquery/jquery_ot_paymentmodulefee.js',
            
            
        ];

        // Loop through and delete the files
        foreach ($files_to_check as $file) {
            if (file_exists($file) && is_writable($file)) {
                unlink($file);
            }
        }
        return true;
    }

}
