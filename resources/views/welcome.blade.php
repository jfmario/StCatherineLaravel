<x-layouts::site
    title="St Catherine Orthodox Church"
    description="Join St. Catherine Orthodox Church in Aiken, SC for Divine Liturgy Sundays at 4:00 PM. Experience Orthodox Christian worship and community."
    :liturgical-color="$liturgicalColor"
>
            <flux:container class="grid gap-12 py-10 lg:grid-cols-[3fr_2fr]">
                <div class="space-y-12">
                    <section>
                        <flux:heading size="xl" level="2" class="mb-3">Welcome to Our Parish</flux:heading>
                        <flux:text size="lg">
                            We are a welcoming Orthodox Christian community in Aiken, South Carolina, dedicated to sharing the love of Christ and the beauty of Orthodox worship.
                        </flux:text>
                    </section>

                    <section>
                        <flux:heading size="xl" level="2" class="mb-3">Visit Us This Sunday</flux:heading>
                        <address class="not-italic">
                            <flux:text>1470 Edgefield Hwy</flux:text>
                            <flux:text>Aiken, SC 29801</flux:text>
                            <flux:text class="mt-2">
                                <flux:link href="https://maps.google.com/?q=1470%20Edgefield%20Hwy%2C%20Aiken%2C%20SC%2029801">View on Google Maps</flux:link>
                            </flux:text>
                            <flux:text class="mt-2">Phone: (803) 439-1909</flux:text>
                            <flux:text>Email: fgrogers@aol.com</flux:text>
                        </address>
                    </section>
                </div>

                <section>
                    <flux:heading size="xl" level="2" class="mb-3">Schedule</flux:heading>
                    <flux:text class="mb-6">
                        This is our regular schedule. See our <flux:link href="#">calendar</flux:link> for any adjustments or additional events.
                    </flux:text>
                    <div class="space-y-6">
                        <flux:card class="space-y-2">
                            <flux:heading size="lg" level="3">Regular Services</flux:heading>
                            <flux:text>9th Hour: Sun 3:45 PM</flux:text>
                            <flux:text>Divine Liturgy: Sun 4:00 PM</flux:text>
                            <flux:text>Coffee hour and fellowship follows</flux:text>
                            <flux:text>Great Vespers*: Sat 5:00 PM</flux:text>
                            <flux:text size="sm">*Not held on 2nd Saturday of month</flux:text>
                        </flux:card>
                        <flux:card class="space-y-2">
                            <flux:heading size="lg" level="3">Study Groups</flux:heading>
                            <div>
                                <flux:text>Bible Study: Monday 6:30 PM</flux:text>
                                <flux:text size="sm">At the church and via Zoom</flux:text>
                            </div>
                            <div>
                                <flux:text>Catechism Class: Tuesday 6:30 PM</flux:text>
                                <flux:text size="sm">Via Zoom</flux:text>
                            </div>
                        </flux:card>
                        <flux:card class="space-y-2">
                            <flux:heading size="lg" level="3">Women's Group</flux:heading>
                            <flux:text>2nd Thursday: 1:30 PM</flux:text>
                            <flux:text>4th Thursday: 6:30 PM</flux:text>
                        </flux:card>
                    </div>
                </section>
            </flux:container>
</x-layouts::site>
