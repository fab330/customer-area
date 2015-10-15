<?php /** Template version: 1.0.0 */ ?>

<?php
/*  Copyright 2013 MarvinLabs (contact@marvinlabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
?>

<?php /** @var $address array */ ?>
<?php /** @var $address_id string */ ?>
<?php /** @var $address_class string */ ?>
<?php /** @var $address_label string */ ?>

<h3><?php echo $address_label; ?></h3>

<div class="cuar-address cuar-<?php echo $address_class; ?>">
    <p>
        <?php if ( !empty($address['company'])) : ?>
            <strong><?php echo $address['company']; ?></strong><br>
        <?php endif; ?>

        <?php if ( !empty($address['name'])) : ?>
            <strong><?php echo $address['name']; ?></strong>
        <?php endif; ?>
    </p>

    <p>
        <?php if ( !empty($address['line1'])) : ?>
            <?php echo $address['line1']; ?><br>
        <?php endif; ?>
        <?php if ( !empty($address['line2'])) : ?>
            <?php echo $address['line2']; ?><br>
        <?php endif; ?>
        <?php if ( !empty($address['zip']) || !empty($address['city'])) : ?>
            <?php echo $address['zip']; ?>&nbsp;<?php echo $address['city']; ?><br>
        <?php endif; ?>
        <?php if ( !empty($address['state']) || !empty($address['country'])) : ?>
            <?php if ( !empty($address['state'])) : ?><?php echo CUAR_CountryHelper::getStateName($address['country'],
                $address['state']); ?>,&nbsp;<?php endif; ?>
            <?php echo CUAR_CountryHelper::getCountryName($address['country']); ?>
        <?php endif; ?>
    </p>
</div>