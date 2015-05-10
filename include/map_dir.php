<?

global $pennsic_number;
global $map_dir;

$map_dir = "maps/${pennsic_number}";

if (! is_dir($map_dir)) {
    ?>
<h1 style="color:red">Note: map files missing; using last year&rsquo;s maps</h1>
    <?
    $map_dir = "maps/" . ($pennsic_number-1);

    if (! is_dir($map_dir)) {
        ?>
<h2 style="color:red">... which are also missing!</h1>
        <?
        $map_dir = "maps/missing";
    }
}
?>