import { ApplicationConfig, inject } from "@angular/core";
import { ApolloClientOptions, InMemoryCache } from "@apollo/client/core";
import { Apollo, APOLLO_OPTIONS } from "apollo-angular";
import { HttpLink } from "apollo-angular/http";
import { environment } from "../environments/environment.development";

const uri = environment.graphQLEndpoint;
export function apolloOptionsFactory(): ApolloClientOptions<any> {
  const httpLink = inject(HttpLink);
  return {
    link: httpLink.create({ uri }),
    cache: new InMemoryCache(),
  };
}

export const GraphQLModule: ApplicationConfig["providers"] = [
  Apollo,
  {
    provide: APOLLO_OPTIONS,
    useFactory: apolloOptionsFactory,
  },
];
