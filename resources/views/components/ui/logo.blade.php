{{--
    Stickle brand mark: a monoline S inscribed in a ring, touching it at four
    points -- the crown, the base, and both terminals, each exactly `r` from
    centre so they land on the ring's centreline.

    The ring carries a comet gradient that falls from 100% to 50% opacity across
    a full revolution, rotating clockwise.

    Colour comes from `currentColor`, so a parent text-* class drives it and dark
    mode needs no second asset. Inline rather than an <img>: no request, no
    external host. The previous mark was a Tailwind UI placeholder served from
    tailwindui.com.

    Animation honours prefers-reduced-motion (see resources/css/app.css).
--}}
<svg
    {{ $attributes->merge(['class' => 'stickle-mark size-8']) }}
    viewBox="0 0 60 60"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    role="img"
    aria-label="Stickle"
>
    <g class="stickle-mark__trail">
        <circle cx="30" cy="30" r="22" stroke="currentColor" stroke-width="3" />
    </g>

    {{-- Two elliptical bowls, rx 12 / ry 8.5, centred at (30,21.5) and (30,38.5).
         They meet exactly at the waist (30,30).

         Everything is driven by stroke edges, not centrelines -- that was what
         made an earlier version cross the ring. The ring's 3 stroke puts its
         inner edge at 20.5; the S's 7 stroke reaches 3.5 past its centreline;
         so the bowls are tangent to radius 17, and 17 + 3.5 = 20.5 exactly.
         The S kisses the inside of the ring without crossing it.

         rx is capped at 12: past ~12.02 the ellipse's widest point overtakes the
         crown and bulges through the ring. Arcs rather than beziers, so the
         tangency is exact rather than eyeballed. --}}
    <path
        d="M41.41 18.87A12 8.5 0 1 0 30 30A12 8.5 0 1 1 18.59 41.13"
        stroke="currentColor"
        stroke-width="7"
        stroke-linecap="butt"
    />
</svg>
