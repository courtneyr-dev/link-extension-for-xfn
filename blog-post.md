# The Web Remembers Who We Are: Why I Built an XFN Plugin in 2025

Here's something I've been thinking about: we've spent the last fifteen years building elaborate platforms to tell the internet who our friends are, what we're eating, and which former classmates we'd rather avoid. We handed our social graphs to corporations who promptly monetized them, sold them, and occasionally leaked them. And the whole time, buried in the HTML spec, sitting there since 2003 like a patient houseplant, was a perfectly good way to just... say how we know people.

It's called XFN. XHTML Friends Network. And I built a WordPress plugin for it because I went down a fediverse rabbit hole and realized we've been doing this wrong.

## The Spark

Look, I'll be honest. I didn't wake up one morning thinking "the world needs more microformats." I got to XFN the way most of us get to old web standards: by accident, through Mastodon, while trying to understand why `rel="me"` actually matters.

If you're on Mastodon or anywhere in the fediverse, you've probably done the little verification dance. You put a link on your website back to your Mastodon profile with `rel="me"`, and Mastodon checks that your site links back to your profile, and boom—you get that satisfying green checkmark. No central authority. No verification badge marketplace. Just two sites pointing at each other saying "yep, same person."

That's when it clicked for me. Not the mechanism—I understood the mechanism. It was the *philosophy*. We don't need Twitter (sorry, "X") to tell us who we are. We don't need Meta to map our social graphs. We just need websites that can say "I know this person" and "this is me" in a language browsers already speak.

So I started digging into `rel` attributes, which led me to microformats, which led me to the IndieWeb, which led me—inevitably—to XFN. And reader, I fell hard.

## What Even Is XFN?

XFN is beautifully simple. You know how you link to people on your website? Your blogroll, your "about" page, that list of contributors, the little footer links to your Twitter and GitHub? XFN just adds one tiny attribute to describe *how* you know those people.

Instead of this:
```html
<a href="https://sarahs-blog.com">Sarah</a>
```

You write this:
```html
<a href="https://sarahs-blog.com" rel="friend met">Sarah</a>
```

That's it. You've just told the web that Sarah is someone you consider a friend and have met in person. No database. No API. No OAuth flow. Just plain HTML doing what HTML was designed to do: marking up meaning.

The [XFN specification](https://microformats.org/wiki/xfn) gives you options for friendship levels (contact, acquaintance, friend), professional relationships (co-worker, colleague), family (parent, sibling, spouse, kin), romantic relationships (muse, crush, date, sweetheart), whether you've met physically, and—crucially—`rel="me"` for links to your own content.

XFN was the [first microformat](https://en.wikipedia.org/wiki/XHTML_Friends_Network), introduced in December 2003. It emerged during the blogroll era when people were discovering that the web could be social *without* being a platform. Just people linking to people. Distributed. Messy. Human.

And then Facebook happened. And Twitter happened. And we forgot that the web already knew how to do this.

## Why It Matters in 2025 (Or: The Fediverse Figured Something Out)

Here's what the fediverse gets that corporate social media never will: **identity should be portable, and relationships should be ours.**

When you verify your website on Mastodon using [`rel="me"`](https://docs.joinmastodon.org/user/profile/), you're participating in something quietly revolutionary. You're creating a bidirectional link—a handshake between two places you control—that proves identity without an intermediary. [No central authority hands out "verified" badges](https://fedi.tips/how-do-i-verify-my-account/) in the fediverse because there *is* no central authority. It's just the web, doing what the web does, but finally being trusted to do it.

This is exactly what [Tim Berners-Lee envisioned with the semantic web](https://www.w3.org/standards/semanticweb/): machines reading meaning from documents, relationships expressed in markup, a web where metadata isn't something corporations extract from us but something we assert about ourselves.

And here's where I started having thoughts. Big thoughts. The kind that make you stare at your screen at midnight and whisper, "what if..."

**What if XFN is just `rel="me"` for social graphs?**

Think about it:
- `rel="me"` proves identity across domains
- `rel="friend"` could assert relationships across domains
- `rel="colleague"` could map professional networks without LinkedIn
- `rel="spouse"` could express family connections without Facebook's relationship status dropdown

Right now, Mastodon and other fediverse platforms have to build and maintain their own social graphs. You follow people, they follow back, it's all stored in databases. But what if your blog *already* declared who your friends are? What if a fediverse crawler could read XFN attributes and understand social networks that exist outside any platform?

## The Dream: Distributed Social Graphs

I know what you're thinking. "Courtney, this sounds like the semantic web fantasy that never happened. We've been here before."

Fair. The semantic web has been "five years away" for twenty-five years. But here's what's different now: **the fediverse proved people actually want this.**

We have millions of people who voluntarily left Twitter for a more complicated, federated system because they wanted to own their presence online. People are running their own Mastodon instances, setting up personal websites, adding `rel="me"` links to verify their identity. The IndieWeb community has been doing this quietly for over a decade with [microformats, webmentions, and IndieAuth](https://indieweb.org/XFN).

The appetite is there. The infrastructure is there. What's missing is just... connecting the dots.

Imagine this:
1. **Social discovery through web crawling**: A fediverse instance could crawl your website and see that you've marked ten people as `rel="friend met colleague"`. It could suggest those people to you when you join.

2. **Trust networks**: If three people I've marked as `friend met` all mark you as `friend`, that's a pretty good signal you're a real human I might want to know. Not an algorithm deciding—just the web saying "these connections exist."

3. **Portable relationships**: Your social graph isn't locked in a platform's database. It lives in your HTML. You control it. You can export your WordPress site and your relationships come with you.

4. **Privacy by default**: Unlike Facebook's social graph that assumes everything is fodder for ads, XFN relationships only exist where you publish them. Want to keep your family relationships private? Don't put them in public HTML. Want to shout from the rooftops that someone's your best friend? Add `rel="friend met muse"` and make it semantic.

This isn't science fiction. This is just the web doing what the web was always supposed to do, with standards that have existed for over twenty years.

## Why WordPress? Why Now?

WordPress [powers 43% of the web](https://w3techs.com/technologies/details/cm-wordpress). If we want XFN to actually get used—if we want distributed social graphs to be more than a nerdy dream—it needs to be where people already are, building websites, writing posts, linking to each other.

The block editor (Gutenberg) made this possible in a way the old Classic Editor never could. I could add XFN options directly into the link interface, make it collapsible so it doesn't clutter the UI, add it to Button blocks and Navigation menus and anywhere people link to other humans.

And look, I'm a developer advocate for open source. It's literally my job to help people get their message out, to make technology more accessible. Building this plugin was me practicing what I preach: the web should be open, standards should be usable, and you shouldn't need a computer science degree to participate in the semantic web.

Plus—and I'm just being real here—I was *annoyed*. Annoyed that every conversation about social graphs assumes we need an app for it. Annoyed that we keep rebuilding the same walls around the same gardens when the web already gave us the fence posts.

## What I Actually Built

The [Link Extension for XFN](https://github.com/courtneyr-dev/xfn-link-extension) is a WordPress plugin that adds XFN relationship options to the block editor. Every link—in paragraphs, buttons, navigation menus, lists, embeds—gets a collapsible XFN section where you can mark your relationships.

It's designed to be:
- **Unobtrusive**: Collapsible interface that stays out of your way
- **Accessible**: Full keyboard navigation, screen reader support, WCAG 2.2 AA compliant
- **Standards-based**: Just outputs clean HTML `rel` attributes
- **Plays well with others**: Preserves existing `rel` values like `nofollow` and `noopener`

You can use it right now. It's free, open source, GPL licensed. I built it for personal bloggers and IndieWeb enthusiasts who want their websites to speak the language of relationships.

## The Part Where I Get Idealistic

Here's what keeps me up at night in a good way:

What if we're at an inflection point? What if the corporate social web is actually dying, and the fediverse isn't just a niche but the beginning of something that *takes*?

The pieces are all there:
- **ActivityPub** gives us federated social networking
- **`rel="me"`** gives us distributed identity verification
- **Webmentions** give us cross-site conversations
- **Microformats** give us semantic metadata
- **XFN** gives us portable social graphs

We don't need to invent anything new. We just need to actually *use* what the web already gave us.

And yeah, I know. I'm not naive. Adoption is hard. Standards wars are real. Getting people to care about semantic markup when TikTok exists is an uphill battle. But the fediverse showed us that people will do harder things if they believe in the "why."

The "why" here is pretty compelling: **What if your friendships weren't assets on Meta's balance sheet? What if your professional network wasn't LinkedIn's moat? What if the web just... remembered who we are to each other?**

## What Happens Next

Honestly? I don't know. Maybe this plugin helps five people and three of them are me on different websites. Maybe it sparks something bigger. Maybe someone builds a fediverse bot that crawls XFN relationships and does something wild with them.

What I do know is this: every time someone uses `rel="friend"` or `rel="colleague"` or `rel="me"`, they're participating in a web that's a little more semantic, a little more human, a little more *ours*.

The web has always been better at remembering than we give it credit for. We just have to tell it what matters.

---

## Try It Yourself

If you're running WordPress with the block editor, you can [install the Link Extension for XFN](https://github.com/courtneyr-dev/xfn-link-extension) right now. Add it to your blogroll. Mark your friends. Link to yourself with `rel="me"`. Join the weird little corner of the web that still believes in semantic markup and distributed social graphs.

And if you're building something in the fediverse or IndieWeb space—a crawler, an aggregator, a social reader—consider looking for XFN relationships. Let's see what happens when the web finally gets to be social on its own terms.

The web remembers who we are. We just have to remind it.

---

*Courtney Robertson is an Open Source Developer Advocate who believes in coffee, semantic HTML, and the revolutionary potential of boring web standards. Find her building things at [courtneyr.dev](https://courtneyr.dev) or arguing about web accessibility on Mastodon.*

---

## Sources & Further Reading

- [How to verify your account on Mastodon - Fedi.Tips](https://fedi.tips/how-do-i-verify-my-account/)
- [Mastodon Profile Setup Documentation](https://docs.joinmastodon.org/user/profile/)
- [XFN Specification - Microformats Wiki](https://microformats.org/wiki/xfn)
- [XHTML Friends Network - Wikipedia](https://en.wikipedia.org/wiki/XHTML_Friends_Network)
- [rel-me on IndieWeb](https://indieweb.org/rel-me)
- [XFN on IndieWeb](https://indieweb.org/XFN)
- [Get verified on Mastodon with your website - Opensource.com](https://opensource.com/article/22/11/verified-mastodon-website)
