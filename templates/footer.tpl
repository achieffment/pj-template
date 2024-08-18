        <footer class="container footer">
            {$developer} © — {$smarty.now|date_format:"%Y"}
        </footer>
        {if $footer_scripts}
            <script src="{$footer_scripts}"></script>
        {/if}
    </body>
</html>
