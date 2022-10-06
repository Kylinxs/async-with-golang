{remarksbox type="tip" title="{tr}Tip{/tr}"}{tr}Please see the <a class='alert-link' target='tikihelp' href='http://dev.tiki.org/Performance'>Performance page</a> on Tiki's developer site.{/tr}{/remarksbox}

<form class="admin" id="performance" name="performance" action="tiki-admin.php?page=performance" method="post">
    {ticket}
    <div class="row">
        <div class="mb-3 col-lg-12 clearfix">
            {include file='admin/include_apply_top.tpl'}
        </div>
    </div>

    {tabset}

        {tab name="{tr}Performance{/tr}"}
            <br>
            {preference name=tiki_monitor_performance}
            {preference name=tiki_minify_javascript}
            <div class="adminoptionboxchild" id="tiki_minify_javascript_childcontainer">
                {preference name=tiki_minify_late_js_files}
            </div>
            {preference name=javascript_cdn}
            {preference name=tiki_cdn}
            {preference name=tiki_cdn_ssl}
            {preference name=tiki_cdn_check}
            {preference name=tiki_prefix_css}
            {preference name=tiki_minify_css}
            <div class="adminoptionboxchild" id="tiki_minify_css_childcontainer">
                {preference name=tiki_minify_css_single_file}
            </div>
            {preference name=feature_obzip}
            <div class="adminoptionboxchild">
                {if $gzip_handler ne 'none'}
                    <div class="highlight ms-3">
                        {tr}Output compression is active.{/tr}
                        <br>
                        {tr}Compression is handled by:{/tr} {$gzip_handler}.
                    </div>
                {/if}
            </div>
            {preference name=tiki_cachecontrol_session}
            {preference name=smarty_compilation}
            {preference name=users_serve_avatar_static}
            {preference name=allowImageLazyLoad }

            <fieldset>
                <legend>{tr}PHP settings{/tr}</legend>
                <p>{tr}Some PHP.INI settings that can increase performance{/tr}</p>
                <div class="adminoptionboxchild">
                    <p>
                        {tr _0=$realpath_cache_size_ini}'realpath_cache_size setting': %0{/tr}
                        {tr _0=$realpath_cache_size_percent}(percentage used %0 %{/tr})
                        {help url="php.ini#Performance"
                            desc="realpath_cache_size : {tr}Determines the size of the realpath cache to be used by PHP.{/tr}"}
                    </p>
                    <p>{tr _0=$realpath_cache_ttl}'realpath_cache_ttl setting': %0 seconds{/tr}
                    {help url="php.ini#Performance"
                    desc="realpath_cache_ttl : {tr}Duration of time (in seconds) for which to cache realpath information for a given file or directory.{/tr}"}
                </div>
            </fieldset>
        {/tab}

        {tab name="{tr}Bytecode Cache{/tr}"}
            <br>
            {if $opcode_cache}

                {if !$opcode_compatible}
                    {remarksbox type="warning" title="{tr}Warning{/tr}"}
                    {tr}Some PHP versions may exhibit randomly issues with the OpCache leading to the server starting to fail to serve all PHP requests, your PHP version seems to be affected, despite the performance penalty, we would recommend disabling the OpCache if you experience random crashes.{/tr}
                    {/remarksbox}
                {/if}

                <p>{tr _0=$opcode_cache}Using <strong>%0</strong>. These stats affect all PHP applications running on the server.{/tr}</p>

                {if !empty($opcode_stats.warning_xcache_blocked)}
                    <p>{tr _0="xcache.admin.enable_auth"}Configuration setting %0 prevents from accessing statistics. This will also prevent the cache from being cleared when clearing template cache.{/tr}</p>
                {/if}

                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <td>
                                {wikiplugin _name='chartjs' type=pie id=MemoryGraph width=250 height=100 values=$memory_graph.data data_labels=$memory_graph.data debug=1}
                                {/wikiplugin}
                            </td>
                            <td>
                                {wikiplugin _name='chartjs' type=pie id=CacheGraph width=250 height=100 values=$hits_graph.data data_labels=$hits_graph.data debug=1}
                                {/wikiplugin}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {tr}Memory Used:{/tr} {$opcode_stats.memory_used * 100}% - {tr}Available:{/tr} {$opcode_stats.memory_avail * 100}%
                            </td>
                            <td>
                                {tr}Cache Hits:{/tr} {$opcode_stats.hit_hit * 100}% - {tr}Misses:{/tr} {$opcode_stats.hit_miss * 100}%
                            </td>
                        </tr>
                    </table>
                </div>

                {if !empty($opcode_stats.warning_fresh)}
                    <p>{tr}Few hits recorded. Statistics may not be representative.{/tr}</p>
                {/if}

                {if !empty($opcode_stats.warning_ratio)}
                    <p>{tr _0=$opcode_cache}Low hit ratio. %0 may be misconfigured and not used.{/tr}</p>
                {/if}

                {if !empty($opcode_stats.warning_starve)}
                    <p>{tr}Little memory available. Thrashing likely to occur.{/tr} {tr}The values to increase are apc.shm_size (for APC), xcache.size (for XCache) or opcache.memory_consumption (for OPcache).{/tr}</p>
                {/if}

                {if !empty($opcode_stats.warning_low)}
                    <p>{tr _0=$opcode_cache}Small amount of memory allocated to %0. Verify the configuration.{/tr} {tr}The values to increase are apc.shm_size (for APC), xcache.size (for XCache) or opcache.memory_consumption (for OPcache).{/tr}</p>
                {/if}

                {if !empty($opcode_stats.warning_check)}
                    <p>
                        {tr _0=$stat_flag}Configuration <em>%0</em> is enabled. Disabling modification checks can improve performance, but will require manual clear on file updates.{/tr}
                        {if !empty($opcode_stats.warning_xcache_blocked)}
                            {tr _0=$stat_flag}<em>%0</em> should not be disabled due to authentication on XCache.{/tr}
                        {/if}
                    </p>
                {/if}
                {if !empty($opcode_stats.warning_check)}
                    <p>{tr}Clear all APC caches:{/tr} {self_link apc_clear=true _onclick="confirmPopup('{tr}Clear APC caches?{/tr}', '{ticket mode=get}')"}{tr}Clear Caches{/tr}{/self_link}</p>
                {/if}
            {else}
                {tr}Bytecode cache is not used. Using a bytecode cache (OPcache, APC, XCache, WinCache) is highly recommended for production environments.{/tr}
            {/if}
        {/tab}

        {tab name="{tr}Wiki{/tr}"}
            <br>
            {preference name=wiki_cache}
            {preference name=feature_wiki_icache}
            {preference name=wiki_ranking_reload_probability}
        {/tab}

        {tab name="{tr}Database{/tr}"}
            <br>
            {preference name=log_sql}
            <div class="adminoptionboxchild" id="log_sql_childcontainer">
                {preference name=log_sql_perf_min}
            </div>
            {preference name=feature_search_show_forbidden_obj}
            {preference name=feature_search_show_forbidden_cat}
        {/tab}

        {tab name="{tr}Memcache{/tr}"}
            <br>
            {preference name=memcache_enabled}
            <div class="adminoptionboxchild" id="memcache_enabled_childcontainer">
                {preference name=memcache_prefix}
                {preference name=memcache_expiration}
                {preference name=memcache_servers}
                {preference name=memcache_wiki_data}
                {preference name=memcache_wiki_output}
    