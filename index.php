<?php
/*
Plugin Name: Socialist
Plugin URI: http://www.zuberi.me/wordpress-plugins
Description: A cool utility which helps you to track how many people shared your posts on Facebook, Twitter, LinkedIn and Google Plus.
Version: 1.0
Author: Dor Zuberi (DorZki)
Author URI: http://www.zuberi.me
License: GPL2
*/

/*  Copyright (C) 2011 Dor Zuberi (email : dor@zuberi.me)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once( ABSPATH .'wp-includes/pluggable.php' );

global $wpdb;

add_action( 'admin_menu' , 'socialist_addMenuLink' );

function socialist_addMenuLink()
{
    add_menu_page( 'Socialist', 'Socialist', 'administrator', 'socialist', 'socialist_adminPage' );
}

function socialist_adminPage()
{
    global $wpdb;
    
    $securedID = intval( @$_GET['paged'] );
    $currentPage = ( $securedID < 1 )?1:$securedID;
    $resultsPerPage = 20;
    $startFromRecord = ( $currentPage - 1) * $resultsPerPage;
    
    $sql = "
        SELECT `ID`, `post_title`, `guid`
        FROM `{$wpdb->posts}`
        WHERE `post_status` = 'publish'
            AND `post_type` = 'post'
        ORDER BY `post_date` DESC
        LIMIT {$startFromRecord}, {$resultsPerPage};";
    
    $postsList = $wpdb->get_results( $sql );
    
    if( count( $postsList ) == 0 )
    {
        $socialist_tableRows  = "<tr class='format-default alternate author-self'>";
        $socialist_tableRows .= "   <td colspan='6' style='padding: 5px;'>There are no posts in your database.</td>";
        $socialist_tableRows .= "</tr>";
    }else{
        foreach( $postsList as $postObj )
        {
            $socialist_tableRows .= "<tr class='format-default alternate author-self'>";
            $socialist_tableRows .= "   <td style='padding: 5px; text-align: center;'>{$postObj->ID}</td>";
            $socialist_tableRows .= "   <td style='padding: 5px;'><a href='" . get_permalink( $postObj->ID ) . "'>" . apply_filters( 'the_title', $postObj->post_title ) . "</a></td>";
            $socialist_tableRows .= "   <td style='padding: 5px;'>
                                            <div id='fb-root'></div>
                                            <script>
                                                (function(d, s, id) {
                                                var js, fjs = d.getElementsByTagName(s)[0];
                                                if (d.getElementById(id)) return;
                                                js = d.createElement(s); js.id = id;
                                                js.src = '//connect.facebook.net/en_US/all.js#xfbml=1&appId=203270809695490';
                                                fjs.parentNode.insertBefore(js, fjs);
                                                }(document, 'script', 'facebook-jssdk'));
                                            </script>
                                            <fb:like href='" . get_permalink( $postObj->ID ) . "' send='false' layout='button_count' width='100' show_faces='false' font='arial'></fb:like>
                                        </td>";
            $socialist_tableRows .= "   <td style='padding: 5px;'>
                                            <a href='https://twitter.com/share' class='twitter-share-button' data-url='" . get_permalink( $postObj->ID ) . "' data-text='" . apply_filters( 'the_title', $postObj->post_title ) . "' data-lang='en'>Tweet</a>
                                            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='//platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','twitter-wjs');</script>
                                        </td>";
            $socialist_tableRows .= "   <td style='padding: 5px;'>
                                            <script src='http://platform.linkedin.com/in.js' type='text/javascript'></script>
                                            <script type='IN/Share' data-url='" . get_permalink( $postObj->ID ) . "' data-counter='right'></script>
                                        </td>";
            $socialist_tableRows .= "   <td style='padding: 5px;'>
                                            <script type='text/javascript' src='https://apis.google.com/js/plusone.js'></script>
                                            <g:plusone size='medium' href='" . get_permalink( $postObj->ID ) . "'></g:plusone>
                                        </td>";
            $socialist_tableRows .= "</tr>";
        }
    }
    
    $sql = "
        SELECT `ID`
        FROM `{$wpdb->posts}`
        WHERE `post_status` = 'publish'
            AND `post_type` = 'post';";
            
    $wpdb->query( $sql );
    $totalResults = $wpdb->num_rows;
    
    $totalPages = ceil( $totalResults / $resultsPerPage );
    $isFirstPage = ( $securedID != 1 )?0:$securedID - 1;
    $isLastPage = ( $securedID != $totalPages )?0:$securedID + 1;
    
    if( $isFirstPage == 0 )
    {
        $socialist_navLinks  = "<a href='admin.php?page=socialist' class='first-page disabled' target='_self'>&laquo;</a>&nbsp;";
        $socialist_navLinks .= "<a href='admin.php?page=socialist&paged=1' class='prev-page disabled' target='_self'>&lsaquo;</a>&nbsp;";
    }else{
        $socialist_navLinks .= "<a href='admin.php?page=socialist' class='first-page' target='_self'>&laquo;</a>&nbsp;";
        $socialist_navLinks .= "<a href='admin.php?page=socialist&paged={$isFirstPage}' class='prev-page' target='_self'>&lsaquo;</a>&nbsp;";
    }

    $socialist_navLinks .= "<span class='paging-input'>{$currentPage} of <span class='total-pages'>{$totalPages}</span></span>&nbsp;";
        
    if( $isLastPage == 0 )
    {
        $socialist_navLinks .= "<a href='admin.php?page=socialist&paged={$totalPages}' class='next-page disabled' target='_self'>&rsaquo;</a>&nbsp;";
        $socialist_navLinks .= "<a href='admin.php?page=socialist&paged={$totalPages}' class='last-page disabled' target='_self'>&raquo;</a>";
    }else{
        $socialist_navLinks .= "<a href='admin.php?page=socialist&paged={$isLastPage}' class='next-page' target='_self'>&rsaquo;</a>&nbsp;";
        $socialist_navLinks .= "<a href='admin.php?page=socialist&paged={$totalPages}' class='last-page' target='_self'>&raquo;</a>";
    }
    
    echo "<div class='wrap'>";
    echo "  <div id='icon-edit-pages' class='icon32 icon32-posts-page'><br /></div>";
    echo "  <h2>Socialist Posts List</h2>";
    echo "  <br /><br />";
    echo "  <table class='wp-list-table widefat fixed cloakings' cellspacing='0'>";
    echo "      <thead>";
    echo "          <tr>";
    echo "              <th scope='col' class='manage-column' style='width: 30px;'>ID</th>";
    echo "              <th scope='col' class='manage-column' style='width: 40%;'>Name</th>";
    echo "              <th scope='col' class='manage-column' style='width: 15%;'>Facebook</th>";
    echo "              <th scope='col' class='manage-column' style='width: 15%;'>Twitter</th>";
    echo "              <th scope='col' class='manage-column' style='width: 15%;'>LinkedIn</th>";
    echo "              <th scope='col' class='manage-column' style='width: 15%;'><b>Google+</b></th>";
    echo "          </tr>";
    echo "      </thead>";
    echo "      <tbody>";
    echo            $socialist_tableRows;
    echo "      </tbody>";
    echo "      <tfoot>";
    echo "          <tr>";
    echo "              <th scope='col' class='manage-column' style='width: 30px;'>ID</th>";
    echo "              <th scope='col' class='manage-column' style='width: 40%;'>Name</th>";
    echo "              <th scope='col' class='manage-column' style='width: 15%;'>Facebook</th>";
    echo "              <th scope='col' class='manage-column' style='width: 15%;'>Twitter</th>";
    echo "              <th scope='col' class='manage-column' style='width: 15%;'>LinkedIn</th>";
    echo "              <th scope='col' class='manage-column' style='width: 15%;'><b>Google+</b></th>";
    echo "          </tr>";
    echo "      </tfoot>";
    echo "  </table>";
    echo "  <div class='tablenav bottom'>";
    echo "      <div class='tablenav-pages'>";
    echo            $socialist_navLinks;
    echo "      </div>";
    echo "  </div>";
    echo "</div>";
}
?>